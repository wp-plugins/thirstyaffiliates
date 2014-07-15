var thirstyMCE;
function thirstyOpenLinkPicker(ed) {
	thirstyMCE = ed;

	tb_show("Add an Affiliate Link", thirstyAjaxLink + '?action=thirstyGetThickboxContent&height=640&width=640&TB_iframe=true');
}

function thirstyOpenQuickAddLinkPicker(ed){
	thirstyMCE = ed;

	tb_show("Quick Add Affiliate Link", thirstyAjaxLink + '?action=thirstyGetQuickAddLinkThickboxContent&height=640&width=640&TB_iframe=true');
}

function thirstyInsertLink(linkcode) {
	var richEditorActive = false;
	if (typeof(parent.thirstyMCE) !== 'undefined' && parent.thirstyMCE != null && !parent.thirstyMCE.isHidden()) {
		richEditorActive = true;
	}

	if (richEditorActive) { // Visual editor replacement
		parent.thirstyMCE.focus();
		parent.thirstyMCE.selection.setContent(linkcode);
		parent.thirstyMCE.execCommand('mceRepaint');
	} else { // HTML editor replacement
		thirstyReplaceHTMLEditorSelectedText(linkcode);
	}
}

function thirstyReplaceHTMLEditorSelectedText(text) {
	var el;
	el = parent.document.getElementById("replycontent");
	if (typeof el == "undefined" || !jQuery(el).is(":visible")) // is not a comment reply
		el = parent.document.getElementById("content");

    var sel = parent.thirstyGetHTMLEditorSelection();
    var val = el.value;
    el.value = val.slice(0, sel.start) + text + val.slice(sel.end);
	jQuery(el).trigger('change'); // some addons require notice that something has changed
}

function thirstyDismissLinkPicker() {
	tb_remove();
}
