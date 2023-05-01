<?php


namespace DevBx\Core\Internals;

use Bitrix\Main;
use Bitrix\Main\DB;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Query\Query;

Loc::loadMessages(__FILE__);

abstract class Tree extends Entity\DataManager
{
    const SPACE_ADD = 1;
    const SPACE_REMOVE = 2;

    public static function add(array $data)
    {
        return static::addExtended($data);
    }

    /**
     * Available keys in $additional
     * REBALANCE - if set to true, method will rebalance tree after insertion
     */
    public static function addExtended(array $data, array $additional = array())
    {
        $rebalance = !isset($additional['REBALANCE']) || $additional['REBALANCE'] !== false;

        // determine LEFT_MARGIN, RIGHT_MARGIN and DEPTH_LEVEL
        if ($data['PARENT_ID'] = intval($data['PARENT_ID'])) {
            // if we have PARENT_ID set, just use it`s info
            $node = static::getNodeInfo($data['PARENT_ID']);

            $needResort = true;

            $data['LEFT_MARGIN'] = $node['RIGHT_MARGIN'];
            $data['RIGHT_MARGIN'] = $node['RIGHT_MARGIN'] + 1;
            $data['DEPTH_LEVEL'] = $node['DEPTH_LEVEL'] + 1;
            $data['PARENT_ID'] = $node['ID'];
        } else {
            // otherwise, we assume we have "virtual root node", that has LEFT_MARGIN == 0 and RIGHT_MARGIN == +INFINITY
            // it allows us to have actually a forest, not a tree

            $rm = static::getMaxMargin();
            $needResort = false;

            $data['LEFT_MARGIN'] = $rm > 1 ? $rm + 1 : 1;
            $data['RIGHT_MARGIN'] = $rm > 1 ? $rm + 2 : 2;

            $data['DEPTH_LEVEL'] = 1;
            $data['PARENT_ID'] = 0;
        }

        $addResult = parent::add($data);

        if ($addResult->isSuccess() && $needResort && $rebalance)
            static::rebalance($node, $addResult->getId());

        return $addResult;
    }

    protected static function rebalance($node, $id)
    {
        static::manageFreeSpace($node['RIGHT_MARGIN'], 2, static::SPACE_ADD, $id);
    }

    public static function checkFields(Entity\Result $result, $primary, array $data)
    {
        parent::checkFields($result, $primary, $data);

        if (!($result instanceof Entity\UpdateResult)) // work out only when update()
            return;

        foreach (static::getEntity()->getFields() as $field) {
            if ($field->getName() == 'PARENT_ID' && mb_strlen($data['PARENT_ID'])) {
                //it cant be parent for itself
                if (intval($primary['ID']) == intval($data['PARENT_ID'])) {
                    $result->addError(new Entity\FieldError(
                        $field,
                        Loc::getMessage('DEVBX_CORE_INTERNALS_TREE_ENTITY_CANNOT_MOVE_STRAIGHT_TO_ITSELF_EXCEPTION'),
                        Entity\FieldError::INVALID_VALUE
                    ));
                } else {
                    try {
                        $node = static::getNodeInfo($primary['ID']);
                        $nodeDst = static::getNodeInfo($data['PARENT_ID']);

                        // new parent cannot belong to node subtree
                        if ($node['PARENT_ID'] != $nodeDst['ID']) {
                            if ($nodeDst['LEFT_MARGIN'] >= $node['LEFT_MARGIN'] && $nodeDst['RIGHT_MARGIN'] <= $node['RIGHT_MARGIN']) {
                                $result->addError(new Entity\FieldError(
                                    $field,
                                    Loc::getMessage('DEVBX_CORE_INTERNALS_TREE_ENTITY_CANNOT_MOVE_TO_ITSELF_EXCEPTION'),
                                    Entity\FieldError::INVALID_VALUE
                                ));
                            }
                        }

                    } catch (Main\SystemException $e) {
                    }
                }
            }
        }
    }

    public static function update($primary, array $data)
    {
        return static::updateExtended($primary, $data);
    }

    /**
     * Available keys in $additional
     * REBALANCE - if set to true, method will rebalance tree after insertion
     */
    public static function updateExtended($primary, array $data, array $additional = array())
    {
        $rebalance = !isset($additional['REBALANCE']) || $additional['REBALANCE'] !== false;
        $node = static::getNodeInfo($primary);

        if (isset($data['PARENT_ID']) && !mb_strlen($data['PARENT_ID']))
            $data['PARENT_ID'] = 0;

        $updResult = parent::update($primary, $data);

        // if we have 'PARENT_ID' key in $data, and it was changed, we should relocate subtree
        if ($updResult->isSuccess() && isset($data['PARENT_ID']) && (intval($node['PARENT_ID']) != intval($data['PARENT_ID'])) && $rebalance)
            static::moveSubtree($primary, $data['PARENT_ID']);

        return $updResult;
    }

    public static function delete($primary)
    {
        return static::deleteExtended($primary);
    }

    public static function deleteExtended($primary, array $additional = array()) // here also could be an implementation of CHILDREN_REATTACH
    {
        $rebalance = !isset($additional['REBALANCE']) || $additional['REBALANCE'] !== false;
        $deleteSubtree = !isset($additional['DELETE_SUBTREE']) || $additional['DELETE_SUBTREE'] !== false;

        if ($deleteSubtree) {
            // it means we want to delete not only the following node, but the whole subtree that belongs to it
            // note that with this option set to Y tree structure integrity will be compromised

            $node = static::getNodeInfo($primary);
            if (intval($node['ID'])) {
                static::checkNodeThrowException($node);
                // low-level

                $scopeWhere = static::getScopeWhere();
                if ($scopeWhere)
                    $scopeWhere = ' AND '.$scopeWhere;

                static::getEntity()->getConnection()->query('delete from ' . static::getTableName() . ' as devbx_tree where LEFT_MARGIN > ' . $node['LEFT_MARGIN'] . ' and RIGHT_MARGIN < ' . $node['RIGHT_MARGIN'].$scopeWhere);

                // and also remove free spece, if needed
                if ($rebalance) {
                    static::manageFreeSpace(
                        $node['RIGHT_MARGIN'],
                        ($node['RIGHT_MARGIN'] - $node['LEFT_MARGIN']) + 1,
                        static::SPACE_REMOVE
                    );
                }
            } else {
                throw new Main\SystemException(Loc::getMessage('DEVBX_CORE_INTERNALS_TREE_ENTITY_NODE_NOT_FOUND_EXCEPTION'));
            }
        }

        return parent::delete($primary);
    }

    /**
     * This method is for internal use only. It may be changed without any notification further, or even mystically disappear.
     *
     * @access private
     */
    public static function getSubtreeRangeSqlForNode($primary, $node = array())
    {
        if (empty($node)) {
            $node = static::getNodeInfo($primary);
            if (!intval($node['ID'])) {
                throw new Main\SystemException(Loc::getMessage('DEVBX_CORE_INTERNALS_TREE_ENTITY_NODE_NOT_FOUND_EXCEPTION'));
            }
        }

        static::checkNodeThrowException($node);

        $query = static::query();
        $query->setSelect(array('ID'));
        $query->setFilter(array(
            '>LEFT_MARGIN' => $node['LEFT_MARGIN'],
            '<RIGHT_MARGIN' => $node['RIGHT_MARGIN']
        ));

        return $query->getQuery();
    }

    public static function checkIntegrity()
    {
        return !static::getList([
            'select' => ['ID'],
            'filter' => [
                'LOGIC' => 'OR',
                ['LEFT_MARGIN' => false],
                ['RIGHT_MARGIN' => false]
            ],
            'limit' => 1
        ])->fetch();
    }

    public static function checkNodeIsParentOfNodeById($primary, $childPrimary, $behaviour = array('CHECK_DIRECT' => false))
    {
        return static::checkNodeIsParentOfNodeByCondition(array('=ID' => $primary), array('=ID' => $childPrimary), $behaviour);
    }

    protected static function checkNodeIsParentOfNodeByCondition($parentNodeFilter, $nodeFilter, $behaviour = array('CHECK_DIRECT' => false))
    {
        $parent = static::getList(array('filter' => $parentNodeFilter, 'limit' => 1))->fetch();
        $child = static::getList(array('filter' => $nodeFilter, 'limit' => 1))->fetch();

        if (!intval($parent['ID']))
            throw new Main\SystemException('Node being checked not found');
        if (!intval($child['ID']))
            throw new Main\SystemException('Child node not found');

        if ($behaviour['CHECK_DIRECT'])
            return $parent['ID'] == $child['PARENT_ID'];

        return $parent['LEFT_MARGIN'] < $child['LEFT_MARGIN'] && $parent['RIGHT_MARGIN'] > $child['RIGHT_MARGIN'];
    }

    // recalc left_margin & right_margin in the whole tree
    // strongly recommened to invoke only inside a transaction
    public static function resort()
    {
        $edges = array();
        $nodes = array();

        $res = parent::getList(array('select' => array('ID', 'PARENT_ID', 'LEFT_MARGIN', 'RIGHT_MARGIN')));
        while ($item = $res->Fetch()) {
            $nodes[$item['ID']] = array(
                'LEFT_MARGIN' => $item['LEFT_MARGIN'],
                'RIGHT_MARGIN' => $item['RIGHT_MARGIN']
            );

            if (!intval($item['PARENT_ID']))
                $edges['ROOT'][] = $item['ID'];
            else
                $edges[$item['PARENT_ID']][] = $item['ID'];
        }

        // walk tree in-deep to obtain correct margins
        static::walkTreeInDeep('ROOT', $edges, $nodes, 0, 0);

        foreach ($nodes as $ID=>$updateNode)
        {
            static::update($ID, $updateNode);
        }
    }

    public final static function expectInteger($arg, $argName = '', $customMsg = '')
    {
        $argInt = intval($arg);
        if ($arg != $argInt)
            throw new Main\ArgumentException('invalid argument ' . $argName . ' ' . $customMsg);

        return $argInt;
    }

    public final static function expectIntegerPositive($arg, $argName = '', $customMsg = '')
    {
        $argInt = intval($arg);
        if ($arg != $argInt || $argInt <= 0)
            throw new Main\ArgumentException('invalid integer positive argument ' . $argName . ' ' . $customMsg);

        return $argInt;
    }

    public final static function expectIntegerNonNegative($arg, $argName = '', $customMsg = '')
    {
        $argInt = intval($arg);
        if ($arg != $argInt || $argInt < 0)
            throw new Main\ArgumentException('invalid integer non negative argument ' . $argName . ' ' . $customMsg);

        return $argInt;
    }

    public final static function expectArray($arg, $argName = '', $customMsg = '')
    {
        if (!is_array($arg))
            throw new Main\ArgumentException('invalid array argument ' . $argName . ' ' . $customMsg);

        return $arg;
    }

    public final static function expectNotEmptyArray($arg, $argName = '', $customMsg = '')
    {
        if (!is_array($arg) || empty($arg))
            throw new Main\ArgumentException('invalid not empty array argument ' . $argName . ' ' . $customMsg);

        return $arg;
    }

    public final static function expectStringNotNull($arg, $argName = '', $customMsg = '')
    {
        if ($arg == '')
            throw new Main\ArgumentException('invalid empty string argument ' . $argName . ' ' . $customMsg);

        return (string)$arg;
    }

    public static function getPathToNode($primary, $parameters, $behaviour = array('SHOW_LEAF' => true))
    {
        if ($primary <= 0)
            $primary = static::expectIntegerPositive($primary, '$primary');
        if (!is_array($behaviour))
            $behaviour = array();
        if (!isset($behaviour['SHOW_LEAF']))
            $behaviour['SHOW_LEAF'] = true;

        return static::getPathToNodeByCondition(array('ID' => $primary), $parameters, $behaviour);
    }

    /**
     * Fetches a parent chain of a specified node
     *
     * Available keys in $behaviour
     * SHOW_LEAF : if set to true, return node itself in the result
     *
     * @access private
     */
    public static function getPathToNodeByCondition($filter, $parameters = array(), $behaviour = array('SHOW_LEAF' => true))
    {
        $filter = static::expectNotEmptyArray($filter, '$filter');

        if (!is_array($behaviour))
            $behaviour = array();
        if (!isset($behaviour['SHOW_LEAF']))
            $behaviour['SHOW_LEAF'] = true;

        if (empty($parameters))
            $parameters = array();

        // todo: try to do this job in a single query with join. Speed profit?

        $node = static::getList(array('filter' => $filter, 'limit' => 1))->fetch();
        if (!isset($node['ID']))
            throw new Main\SystemException(Loc::getMessage('DEVBX_CORE_INTERNALS_TREE_ENTITY_NODE_NOT_FOUND_EXCEPTION'));

        $parameters['filter']['<=LEFT_MARGIN'] = intval($node['LEFT_MARGIN']);
        $parameters['filter']['>=RIGHT_MARGIN'] = intval($node['RIGHT_MARGIN']);

        if (!$behaviour['SHOW_LEAF'])
            $parameters['filter']['!=ID'] = $node['ID'];

        $parameters['order'] = array(
            'LEFT_MARGIN' => 'asc'
        );

        return static::getList($parameters);
    }

    public static function getPathToMultipleNodes($nodeInfo = array(), $parameters = array(), $behaviour = array('SHOW_LEAF' => true))
    {
        static::expectNotEmptyArray($nodeInfo, '$nodeInfo');

        if (!is_array($behaviour))
            $behaviour = array();
        if (!isset($behaviour['SHOW_LEAF']))
            $behaviour['SHOW_LEAF'] = true;

        if (empty($parameters))
            $parameters = array();

        if (is_array($parameters['select']))
            $originSelect = $parameters['select'];
        else
            $originSelect = array();

        if (is_array($parameters['order']))
            throw new Main\NotSupportedException('"Order" clause is not supported here');

        $parameters['order'] = array(
            'LEFT_MARGIN' => 'asc'
        );
        $parameters['select'][] = 'ID';
        $parameters['select'][] = 'PARENT_ID';

        $filter = array();
        foreach ($nodeInfo as $node) {
            static::expectNotEmptyArray($node, '$nodeInfo[]');
            $node['ID'] = static::expectIntegerPositive($node['ID'], '$nodeInfo[][ID]');
            $node['LEFT_MARGIN'] = static::expectIntegerNonNegative($node['LEFT_MARGIN'], '$nodeInfo[][LEFT_MARGIN]');
            $node['RIGHT_MARGIN'] = static::expectIntegerPositive($node['RIGHT_MARGIN'], '$nodeInfo[][RIGHT_MARGIN]');

            $filter[] = array(
                '<=LEFT_MARGIN' => intval($node['LEFT_MARGIN']),
                '>=RIGHT_MARGIN' => intval($node['RIGHT_MARGIN'])
            );

            if (!$behaviour['SHOW_LEAF'])
                $filter['!=ID'] = $node['ID'];
        }
        $filter['LOGIC'] = 'OR';

        $parameters['filter'][] = $filter;

        $res = static::getList($parameters);

        $index = array();

        while ($item = $res->Fetch()) {
            $index[$item['ID']] = array(
                'NODE' => $item,
                'PARENT_ID' => $item['PARENT_ID']
            );
        }

        $depthLimit = count($index);

        $pathes = array();
        foreach ($nodeInfo as $node) {
            $path = array();
            $id = $node['ID'];

            $i = 0;
            while ($id) {
                if ($i >= $depthLimit) // there is a cycle or smth like, anyway this is abnormal situation
                    break;

                if (!isset($index[$id])) // non-existing element in the chain. strange, abort
                    break;

                $resultNode = $index[$id]['NODE'];
                if (!in_array('PARENT_ID', $originSelect))
                    unset($resultNode['PARENT_ID']);
                if (!in_array('ID', $originSelect))
                    unset($resultNode['ID']);
                $path[$id] = $resultNode;

                $id = intval($index[$id]['PARENT_ID']);

                $i++;
            }

            $pathes[$node['ID']] = array(
                'ID' => $node['ID'],
                'PATH' => $path
            );
        }

        return new DB\ArrayResult($pathes);
    }

    public static function getDeepestCommonParent($nodeInfo = array(), $parameters = array())
    {
        static::expectNotEmptyArray($nodeInfo, '$nodeInfo');

        $filter = array();

        $min = false;
        $max = false;
        foreach ($nodeInfo as $node) {
            static::expectNotEmptyArray($node, '$nodeInfo[]');
            $node['LEFT_MARGIN'] = static::expectIntegerNonNegative($node['LEFT_MARGIN'], '$nodeInfo[][LEFT_MARGIN]');
            $node['RIGHT_MARGIN'] = static::expectIntegerPositive($node['RIGHT_MARGIN'], '$nodeInfo[][RIGHT_MARGIN]');

            if ($min === false || $node['LEFT_MARGIN'] < $min)
                $min = $node['LEFT_MARGIN'];

            if ($max === false || $node['RIGHT_MARGIN'] > $max)
                $max = $node['RIGHT_MARGIN'];
        }

        if (empty($parameters))
            $parameters = array();

        if (!is_array($parameters['order']))
            $parameters['order'] = array();

        $parameters['filter']['<LEFT_MARGIN'] = $min;
        $parameters['filter']['>RIGHT_MARGIN'] = $max;

        $parameters['order'] = array_merge(array(
            'LEFT_MARGIN' => 'desc',
            'RIGHT_MARGIN' => 'asc'
        ), $parameters['order']);

        $parameters['limit'] = 1;

        return static::getList($parameters);
    }

    public static function getChildren($primary, $parameters = array())
    {
        if (empty($parameters))
            $parameters = array();

        if ($primary = intval($primary)) // here $primary might be unset: in this case we take the first level of a tree
        {
            $node = static::getNodeInfo($primary);

            $parameters['filter']['>=LEFT_MARGIN'] = intval($node['LEFT_MARGIN']);
            $parameters['filter']['<=RIGHT_MARGIN'] = intval($node['RIGHT_MARGIN']);
            $parameters['filter']['!=ID'] = $primary;
            $parameters['filter']['DEPTH_LEVEL'] = intval($node['DEPTH_LEVEL']) + 1;
        } else
            $parameters['filter']['DEPTH_LEVEL'] = 1;

        return static::getList($parameters);
    }

    /**
     * Fetches a set of items which form sub-tree of a given node
     */
    public static function getSubTree($primary, $parameters = array())
    {
        if (empty($parameters))
            $parameters = array();

        if ($primary = intval($primary)) // here $primary might be unset: if so, get the whole tree
        {
            $node = static::getNodeInfo($primary);

            $parameters['filter']['>=LEFT_MARGIN'] = intval($node['LEFT_MARGIN']);
            $parameters['filter']['<=RIGHT_MARGIN'] = intval($node['RIGHT_MARGIN']);
        }

        if (!is_array($parameters['order']) || empty($parameters['order']))
            $parameters['order'] = array('LEFT_MARGIN' => 'asc');

        return static::getList($parameters);
    }

    /**
     * Fetches a chain of parents with their subtrees expanded
     *
     * Available keys in $behaviour
     * SHOW_CHILDREN : if set to true, do return direct ancestors of $primary in the result
     * START_FROM
     */
    public static function getParentTree($primary, $parameters = array(), $behaviour = array('SHOW_CHILDREN' => true, 'START_FROM' => false))
    {
        $primary = static::expectIntegerPositive($primary, '$primary');

        if (!is_array($behaviour))
            $behaviour = array();
        if (!isset($behaviour['SHOW_CHILDREN']))
            $behaviour['SHOW_CHILDREN'] = true;
        if (!isset($behaviour['START_FROM']))
            $behaviour['START_FROM'] = false;

        if (empty($parameters))
            $parameters = array();

        $startFrom = intval($behaviour['START_FROM']);
        $showChildren = $behaviour['SHOW_CHILDREN'];

        if (!$startFrom) {
            $conditions[] = array(
                'DEPTH_LEVEL' => 1
            );
        }

        // todo: combine (1) and (2) in one query, check perfomance change

        // (1)
        $res = static::getPathToNode($primary, array(
            'select' => array('ID')
        ));

        $started = !$startFrom;
        while ($item = $res->Fetch()) {
            if ($item['ID'] == $startFrom)
                $started = true;

            if (!$started)
                continue;

            if (!$showChildren && $item['ID'] == $primary)
                continue;

            $conditions[] = array(
                'PARENT_ID' => $item['ID']
            );
        }

        $conditions['LOGIC'] = 'OR';

        $parameters['filter'][] = $conditions;

        if (!is_array($parameters['order']) || empty($parameters['order']))
            $parameters['order'] = array('LEFT_MARGIN' => 'asc');

        // (2)
        return static::getList($parameters);
    }

    /**
     * @return QueryTree|string
     */
    public static function getQueryClass()
    {
        return QueryTree::class;
    }

    public static function getScopeWhere()
    {
        return static::query()->setCustomBaseTableAlias('devbx_tree')->getScopeWhere();
    }

    /////////////////////////
    /// PROTECTED
    /////////////////////////

    /**
     * Do not call directly, only inside update()
     *
     * @param int $primary Subtree`s root id to move
     * @param int $primaryDst Item id to attach our subtree to
     *
     */
    protected final static function moveSubtree($primary, $primaryDst)
    {
        $node = static::getNodeInfo($primary);

        if (!($primaryDst = intval($primaryDst))) // move to root
        {
            $rm = static::getMaxMargin();

            $lDst = $rm + 1;
            $rDst = $rm + 2;
            $dDst = 0;
        } else {
            $nodeDst = static::getNodeInfo($primaryDst);

            $lDst = intval($nodeDst['LEFT_MARGIN']);
            $rDst = intval($nodeDst['RIGHT_MARGIN']);
            $dDst = intval($nodeDst['DEPTH_LEVEL']);
        }

        $lSub = intval($node['LEFT_MARGIN']);
        $rSub = intval($node['RIGHT_MARGIN']);
        $dSub = intval($node['DEPTH_LEVEL']);

        $tableName = static::getTableName();

        $scopeWhere = static::getScopeWhere();
        if ($scopeWhere)
            $scopeWhere = ' AND '.$scopeWhere;

        $sql = "update " . $tableName . " devbx_tree set 

				DEPTH_LEVEL	= 
					case 
						when 
							LEFT_MARGIN between {$lSub} and {$rSub} 
						then 
							DEPTH_LEVEL + " . ($dDst - $dSub + 1) . "

						else 
							DEPTH_LEVEL 

					end, ";

        // DO NOT switch the column update order in the code below, it WILL NOT work correctly

        // subtree moves upwards along it`s path
        if ($lDst < $lSub && $rDst > $rSub && $dDst < ($dSub - 1)) {
            $sql .= "

					RIGHT_MARGIN = 
						case 
							when
								RIGHT_MARGIN between " . ($rSub + 1) . " and " . ($rDst - 1) . "
							then
								RIGHT_MARGIN - " . ($rSub - $lSub + 1) . "

							when 
								LEFT_MARGIN between " . $lSub . " and " . $rSub . "
							then 
								RIGHT_MARGIN + " . ((($rDst - $rSub - $dSub + $dDst) / 2) * 2 + $dSub - $dDst - 1) . "

							else RIGHT_MARGIN
						end,

					LEFT_MARGIN =
						case 
							when 
								LEFT_MARGIN between " . ($rSub + 1) . " and " . ($rDst - 1) . " 
							then 
								LEFT_MARGIN - " . ($rSub - $lSub + 1) . " 

							when 
								LEFT_MARGIN between " . $lSub . " and " . $rSub . "
							then 
								LEFT_MARGIN + " . ((($rDst - $rSub - $dSub + $dDst) / 2) * 2 + $dSub - $dDst - 1) . "

							else 
								LEFT_MARGIN 
						end

					where LEFT_MARGIN between " . ($lDst + 1) . " and " . ($rDst - 1);
        } elseif ($lDst < $lSub) // subtree moves to the left of it`s path (to the left branch)
        {
            $sql .= "

					LEFT_MARGIN	= 
						case 
							when 
								LEFT_MARGIN between " . $rDst . " and " . ($lSub - 1) . "
							then 
								LEFT_MARGIN + " . ($rSub - $lSub + 1) . "

							when 
								LEFT_MARGIN between " . $lSub . " and " . $rSub . " 
							then 
								LEFT_MARGIN - " . ($lSub - $rDst) . "

							else 
								LEFT_MARGIN 
						end, 

					RIGHT_MARGIN = 
						case 
							when 
								RIGHT_MARGIN between " . $rDst . " and " . $lSub . " 
							then
								RIGHT_MARGIN + " . ($rSub - $lSub + 1) . "

							when
								RIGHT_MARGIN between " . $lSub . " and " . $rSub . "
							then
								RIGHT_MARGIN - " . ($lSub - $rDst) . " 

							else 
								RIGHT_MARGIN
						end 

					where LEFT_MARGIN between " . $lDst . " and " . $rSub . " or RIGHT_MARGIN between " . $lDst . " and " . $rSub;
        } else // subtree moves to the right of it`s path (to the right branch)
        {
            $sql .= "

					LEFT_MARGIN	=
						case 
							when 
								LEFT_MARGIN between " . $rSub . " and " . $rDst . " 
							then 
								LEFT_MARGIN - " . ($rSub - $lSub + 1) . "

							when 
								LEFT_MARGIN between " . $lSub . " and " . $rSub . "
							then 
								LEFT_MARGIN + " . ($rDst - $rSub - 1) . " 
							
							else 
								LEFT_MARGIN 
						end, 

					RIGHT_MARGIN = 
						case 
							when 
								RIGHT_MARGIN between " . ($rSub + 1) . " and " . ($rDst - 1) . "
							then RIGHT_MARGIN - " . ($rSub - $lSub + 1) . "

							when 
								RIGHT_MARGIN between " . $lSub . " and " . $rSub . "
							then RIGHT_MARGIN + " . ($rDst - $rSub - 1) . " 

							else RIGHT_MARGIN
					end 

				where LEFT_MARGIN between " . $lSub . " and " . $rDst . " or RIGHT_MARGIN between " . $lSub . " and " . $rDst;
        }

        static::getEntity()->getConnection()->query($sql.$scopeWhere);
    }

    protected final static function manageFreeSpace($right, $length = 2, $op = self::SPACE_ADD, $exceptId = false)
    {
        if ($length <= 1 || $right <= 0)
            return;

        // LEFT_MARGIN & RIGHT_MARGIN are system fields, user should not know about them ever, so no orm events needed to be fired on update of them

        $sign = $op == self::SPACE_ADD ? '+' : '-';

        $tableName = static::getTableName();
        $exceptId = intval($exceptId);

        $query = "update {$tableName} devbx_tree set 
			LEFT_MARGIN = case when LEFT_MARGIN > {$right} then LEFT_MARGIN {$sign} {$length} else LEFT_MARGIN end,
			RIGHT_MARGIN = case when RIGHT_MARGIN >= {$right} then RIGHT_MARGIN {$sign} {$length} else RIGHT_MARGIN end 
			where RIGHT_MARGIN >= {$right}" . ($exceptId ? " and ID <> {$exceptId}" : "");

        $scopeWhere = static::getScopeWhere();
        if ($scopeWhere)
            $scopeWhere = ' AND '.$scopeWhere;

        $shifted = static::getEntity()->getConnection()->query($query.$scopeWhere);

        if (!$shifted)
            throw new Main\SystemException('Query failed: managing free space in a tree', 0, __FILE__, __LINE__); // SaleTreeSystemException
    }

    // in-deep tree walk
    protected final static function walkTreeInDeep($primary, $edges, &$nodes, $margin, $depth = 0)
    {
        $lMargin = $margin;

        if (empty($edges[$primary]))
            $rMargin = $margin + 1;
        else {
            $offset = $margin + 1;
            foreach ($edges[$primary] as $sNode)
                $offset = static::walkTreeInDeep($sNode, $edges, $nodes, $offset, $depth + 1);

            $rMargin = $offset;
        }

        // update !
        if ($primary != 'ROOT') {
            $nodes[$primary]['LEFT_MARGIN'] = intval($lMargin);
            $nodes[$primary]['RIGHT_MARGIN'] = intval($rMargin);
            $nodes[$primary]['DEPTH_LEVEL'] = $depth;
        }

        return $rMargin + 1;
    }

    protected static function applyRestrictions(&$data)
    {
        unset($data['LEFT_MARGIN']);
        unset($data['RIGHT_MARGIN']);
        unset($data['DEPTH_LEVEL']);
    }

    protected static function getNodeInfo($primary)
    {
        if (is_array($primary))
            $primary = reset($primary);

        $primary = static::expectIntegerPositive($primary, '$primary');

        $node = static::getById($primary)->fetch();
        if (!isset($node['ID'])) {
            throw new Main\SystemException(Loc::getMessage('DEVBX_CORE_INTERNALS_TREE_ENTITY_NODE_NOT_FOUND_EXCEPTION'));
        }

        return $node;
    }

    protected static function getMaxMargin()
    {
        $res = static::query()
            ->addSelect('RIGHT_MARGIN')
            ->addOrder('RIGHT_MARGIN', 'DESC')
            ->setLimit(1)
            ->fetch();

        return intval($res['RIGHT_MARGIN']);
    }

    protected static function checkNodeThrowException($node)
    {
        // left margin MAY be equal to zero, right margin MAY NOT
        if (!is_numeric($node['LEFT_MARGIN']) || (int)$node['LEFT_MARGIN'] < 0 || !intval($node['RIGHT_MARGIN']) || !intval($node['ID'])) {
            throw new Main\SystemException('invalid node data ' . print_r($node, true));
        }
    }

}
