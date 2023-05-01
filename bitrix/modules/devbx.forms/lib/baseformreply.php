<?php

namespace DevBx\Forms;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use DevBx\Core\Assert;

Loc::loadMessages(__FILE__);

class BaseFormReplyTable extends Entity\DataManager
{
    const SPACE_ADD = 1;
    const SPACE_REMOVE = 2;

    public static function onBeforeAdd(Entity\Event $event)
    {
        global $USER;

        $result = new Entity\EventResult;

        $userId = is_object($USER) && $USER->IsAuthorized() ? $USER->GetID() : 0;
        $now = new DateTime();

        $result->modifyFields(array('CREATED_USER_ID' => $userId, 'CREATED_DATE' => $now, 'MODIFIED_USER_ID' => $userId, 'MODIFIED_DATE' => $now));

        return $result;
    }

    public static function onBeforeUpdate(Entity\Event $event)
    {
        global $USER;

        $result = new Entity\EventResult;

        $userId = is_object($USER) && $USER->IsAuthorized() ? $USER->GetID() : 0;

        $result->modifyFields(array('MODIFIED_USER_ID' => $userId, 'MODIFIED_DATE' => new DateTime()));

        return $result;
    }

    public static function add(array $data)
    {
        return self::addExtended($data);
    }

    /**
     * Available keys in $additional
     * REBALANCE - if set to true, method will rebalance tree after insertion
     */
    public static function addExtended(array $data, array $additional = array())
    {
        $data['RESULT_ID'] = Assert::expectIntegerPositive($data['RESULT_ID'], '$data[RESULT_ID]');

        $rebalance = !isset($additional['REBALANCE']) || $additional['REBALANCE'] !== false;
        $node = false;

        // determine LEFT_MARGIN, RIGHT_MARGIN and DEPTH_LEVEL
        if ($data['PARENT_ID'] = intval($data['PARENT_ID'])) {
            // if we have PARENT_ID set, just use it`s info
            $node = self::getNodeInfo($data['PARENT_ID']);

            $needResort = true;

            $data['LEFT_MARGIN'] = $node['RIGHT_MARGIN'];
            $data['RIGHT_MARGIN'] = $node['RIGHT_MARGIN'] + 1;
            $data['DEPTH_LEVEL'] = $node['DEPTH_LEVEL'] + 1;
            $data['PARENT_ID'] = $node['ID'];
        } else {
            // otherwise, we assume we have "virtual root node", that has LEFT_MARGIN == 0 and RIGHT_MARGIN == +INFINITY
            // it allows us to have actually a forest, not a tree

            $rm = self::getMaxMargin($data['RESULT_ID']);
            $needResort = false;

            $data['LEFT_MARGIN'] = $rm > 1 ? $rm + 1 : 1;
            $data['RIGHT_MARGIN'] = $rm > 1 ? $rm + 2 : 2;

            $data['DEPTH_LEVEL'] = 1;
            $data['PARENT_ID'] = 0;
        }

        $addResult = parent::add($data);

        if ($addResult->isSuccess() && $needResort && $rebalance && $node)
            self::rebalance($node, $addResult->getId());

        return $addResult;
    }

    protected static function getNodeInfo($primary)
    {
        $primary = Assert::expectIntegerPositive($primary, '$primary');

        $node = self::getById($primary)->fetch();
        if (!isset($node['ID'])) {
            throw new Main\SystemException(Loc::getMessage('DEVBX_FORMS_BASE_FORM_REPLY_ENTITY_NODE_NOT_FOUND_EXCEPTION'));
        }

        return $node;
    }

    protected static function getMaxMargin($resultId)
    {
        $res = static::query()
            ->addSelect('RIGHT_MARGIN')
            ->addOrder('RIGHT_MARGIN', 'DESC')
            ->where('RESULT_ID', $resultId)
            ->setLimit(1)
            ->fetch();

        return intval($res['RIGHT_MARGIN']);
    }

    protected static function rebalance($node, $id)
    {
        self::manageFreeSpace($node['RESULT_ID'], $node['RIGHT_MARGIN'], 2, self::SPACE_ADD, $id);
    }

    protected final static function manageFreeSpace($resultId, $right, $length = 2, $op = self::SPACE_ADD, $exceptId = false)
    {
        if ($length <= 1 || $right <= 0)
            return;

        // LEFT_MARGIN & RIGHT_MARGIN are system fields, user should not know about them ever, so no orm events needed to be fired on update of them

        $sign = $op == self::SPACE_ADD ? '+' : '-';

        $tableName = static::getTableName();
        $resultId = intval($resultId);
        $exceptId = intval($exceptId);

        $query = "update {$tableName} set 
			LEFT_MARGIN = case when LEFT_MARGIN > {$right} then LEFT_MARGIN {$sign} {$length} else LEFT_MARGIN end,
			RIGHT_MARGIN = case when RIGHT_MARGIN >= {$right} then RIGHT_MARGIN {$sign} {$length} else RIGHT_MARGIN end 
			where RIGHT_MARGIN >= {$right}" . ($exceptId ? " and ID <> {$exceptId}" : "") . ' and RESULT_ID = ' . $resultId;

        $shifted = static::getEntity()->getConnection()->query($query);

        if (!$shifted)
            throw new Main\SystemException('Query failed: managing free space in a tree', 0, __FILE__, __LINE__); // SaleTreeSystemException
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
                        Loc::getMessage('DEVBX_FORMS_BASE_FORM_REPLY_ENTITY_CANNOT_MOVE_STRAIGHT_TO_ITSELF_EXCEPTION'),
                        Entity\FieldError::INVALID_VALUE
                    ));
                } else {
                    try {
                        $node = self::getNodeInfo($primary['ID']);
                        $nodeDst = self::getNodeInfo($data['PARENT_ID']);

                        // new parent cannot belong to node subtree
                        if ($node['PARENT_ID'] != $nodeDst['ID']) {
                            if ($nodeDst['LEFT_MARGIN'] >= $node['LEFT_MARGIN'] && $nodeDst['RIGHT_MARGIN'] <= $node['RIGHT_MARGIN']) {
                                $result->addError(new Entity\FieldError(
                                    $field,
                                    Loc::getMessage('DEVBX_FORMS_BASE_FORM_REPLY_ENTITY_CANNOT_MOVE_TO_ITSELF_EXCEPTION'),
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
        return self::updateExtended($primary, $data);
    }

    /**
     * Available keys in $additional
     * REBALANCE - if set to true, method will rebalance tree after insertion
     */
    public static function updateExtended($primary, array $data, array $additional = array())
    {
        $rebalance = !isset($additional['REBALANCE']) || $additional['REBALANCE'] !== false;
        $node = self::getNodeInfo($primary);

        if (isset($data['PARENT_ID']) && !mb_strlen($data['PARENT_ID']))
            $data['PARENT_ID'] = 0;

        $updResult = parent::update($primary, $data);

        // if we have 'PARENT_ID' key in $data, and it was changed, we should relocate subtree
        if ($updResult->isSuccess() && isset($data['PARENT_ID']) && (intval($node['PARENT_ID']) != intval($data['PARENT_ID'])) && $rebalance)
            self::moveSubtree($primary, $data['PARENT_ID']);

        return $updResult;
    }

    /**
     * Do not call directly, only inside update()
     *
     * @param int $primary Subtree`s root id to move
     * @param int $primaryDst Item id to attach our subtree to
     *
     */
    protected final static function moveSubtree($primary, $primaryDst)
    {
        $node = self::getNodeInfo($primary);

        if (!($primaryDst = intval($primaryDst))) // move to root
        {
            $rm = self::getMaxMargin($node['RESULT_ID']);

            $lDst = $rm + 1;
            $rDst = $rm + 2;
            $dDst = 0;
        } else {
            $nodeDst = self::getNodeInfo($primaryDst);

            $lDst = intval($nodeDst['LEFT_MARGIN']);
            $rDst = intval($nodeDst['RIGHT_MARGIN']);
            $dDst = intval($nodeDst['DEPTH_LEVEL']);
        }

        $lSub = intval($node['LEFT_MARGIN']);
        $rSub = intval($node['RIGHT_MARGIN']);
        $dSub = intval($node['DEPTH_LEVEL']);

        $tableName = static::getTableName();

        $sql = "update " . $tableName . " set 

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

        static::getEntity()->getConnection()->query($sql . ' and RESULT_ID = ' . $node['RESULT_ID']);
    }

    public static function delete($primary)
    {
        return static::deleteExtended($primary);
    }

    // recalc left_margin & right_margin in the whole tree
    // strongly recommened to invoke only inside a transaction

    public static function deleteExtended($primary, array $additional = array()) // here also could be an implementation of CHILDREN_REATTACH
    {
        $rebalance = !isset($additional['REBALANCE']) || $additional['REBALANCE'] !== false;
        $deleteSubtree = !isset($additional['DELETE_SUBTREE']) || $additional['DELETE_SUBTREE'] !== false;

        if ($deleteSubtree) {
            // it means we want to delete not only the following node, but the whole subtree that belongs to it
            // note that with this option set to Y tree structure integrity will be compromised

            $node = self::getNodeInfo($primary);
            if (intval($node['ID'])) {
                static::checkNodeThrowException($node);
                // low-level

                static::getEntity()->getConnection()->query('delete from ' . static::getTableName() . ' where LEFT_MARGIN > ' . $node['LEFT_MARGIN'] . ' and RIGHT_MARGIN < ' . $node['RIGHT_MARGIN'] . ' and RESULT_ID = ' . $node['RESULT_ID']);

                // and also remove free spece, if needed
                if ($rebalance) {
                    self::manageFreeSpace(
                        $node['RESULT_ID'],
                        $node['RIGHT_MARGIN'],
                        ($node['RIGHT_MARGIN'] - $node['LEFT_MARGIN']) + 1,
                        self::SPACE_REMOVE
                    );
                }
            } else {
                throw new Main\SystemException(Loc::getMessage('DEVBX_FORMS_BASE_FORM_REPLY_ENTITY_NODE_NOT_FOUND_EXCEPTION'));
            }
        }

        return parent::delete($primary);
    }

    protected static function checkNodeThrowException($node)
    {
        // left margin MAY be equal to zero, right margin MAY NOT
        if (!is_numeric($node['LEFT_MARGIN']) || (int)$node['LEFT_MARGIN'] < 0 || !intval($node['RIGHT_MARGIN']) || !intval($node['ID'])) {
            throw new Main\SystemException('invalid node data ' . print_r($node, true));
        }
    }

    public static function resort($resultId)
    {
        $edges = array();
        $nodes = array();

        $res = parent::getList(array('filter'=>array('=RESULT_ID'=>$resultId),'select' => array('ID', 'PARENT_ID', 'LEFT_MARGIN', 'RIGHT_MARGIN')));
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
        self::walkTreeInDeep('ROOT', $edges, $nodes, 0, 0);

        foreach ($nodes as $ID=>$updateNode)
        {
            self::update($ID, $updateNode);
        }
    }

    protected final static function walkTreeInDeep($primary, $edges, &$nodes, $margin, $depth = 0)
    {
        $lMargin = $margin;

        if (empty($edges[$primary]))
            $rMargin = $margin + 1;
        else {
            $offset = $margin + 1;
            foreach ($edges[$primary] as $sNode)
                $offset = self::walkTreeInDeep($sNode, $edges, $nodes, $offset, $depth + 1);

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
}

