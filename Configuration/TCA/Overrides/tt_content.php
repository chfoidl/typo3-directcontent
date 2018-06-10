<?php

call_user_func(
    function () {
        $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['itemsProcFunc'] = \Sethorax\DirectContent\View\BackendLayoutView::class . '->colPosListItemProcFunc';
    }
);