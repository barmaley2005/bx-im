{"version":3,"file":"text.map.js","names":["BX","namespace","escapeText","Landing","Utils","headerTagMatcher","Matchers","headerTag","changeTagName","textToPlaceholders","Block","Node","Text","options","Runtime","loadExtension","apply","this","arguments","type","tableBaseFontSize","onClick","bind","onPaste","onDrop","onInput","onKeyDown","onMousedown","onMouseup","node","addEventListener","document","currentNode","prototype","__proto__","superClass","constructor","onAllowInlineEdit","setAttribute","Loc","getMessage","onChange","preventAdjustPosition","preventHistory","call","UI","Panel","EditorPanel","getInstance","adjustPosition","History","push","event","code","onBackspaceDown","clearTimeout","inputTimeout","key","keyCode","which","top","window","navigator","userAgent","match","ctrlKey","metaKey","setTimeout","lastValue","getValue","isTable","tableFontSize","parseInt","getComputedStyle","srcElement","getPropertyValue","textContent","classList","contains","add","remove","onEscapePress","isEditable","hide","disableEdit","preventDefault","clipboardData","getData","sourceText","encodedText","encode","isLinkPasted","prepareToLink","formattedHtml","replace","RegExp","execCommand","text","onDocumentClick","fromNode","manifest","group","allowInlineEdit","Main","isControlsEnabled","stopPropagation","enableEdit","querySelectorAll","forEach","table","hasAttribute","prepareNewTable","textOnly","StylePanel","isShown","show","buttons","nodeTableContainerList","tableContainer","tableEditor","unselect","Tool","ColorPicker","hideAll","Button","FontAction","requestAnimationFrame","target","nodeName","parentElement","range","createRange","selectNode","getSelection","removeAllRanges","addRange","addTableButtons","isContentEditable","length","nodeTableContainer","TableEditor","default","getDesignButton","isHeader","getChangeTagButton","onChangeHandler","onChangeTag","contentEditable","designButton","Design","html","attrs","title","onDesignShow","isAllowInlineEdit","getField","field","Field","selector","name","content","innerHTML","changeTagButton","setValue","value","preventSave","isSavePrevented","querySelector","cloneNode","prepareTable","test","nodeIsTable","tdTag","neededButtons","setTd","tableButtons","getTableButtons","tableAlignButtons","isCell","isButtonAddRow","isButtonAddCol","isNeedTablePanel","hideButtons","nodeTableList","nodeTable","tableButton","parentNode","children","Array","from","getAmountTableRows","neededButon","childNodes","childNodesArray","childNodesArrayPrepare","childNode","nodeType","neededPosition","indexOf","rows","row","rowChildPrepare","rowChildNode","getAmountTableCols","isSelectedAll","th","insertAfter","activeAlignButtonId","setActiveAlignButtonId","undefined","count","isIdentical","tableAlignButton","id","layout","ChangeTag","toLowerCase","activateItem","AlignTable","ColorAction","DeleteElementTable","StyleTable","CopyTable","DeleteTable","data","changeOptionsHandler","setClassesForRemove","className","element","selection","position","getRangeAt","startOffset","focusNode","Type","isNil","firstChild","focusNodeParent","allowedNodeName","includes","focusNodeContainer","createElement","append","contentNode","after","reg"],"sources":["text.js"],"mappings":"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"}