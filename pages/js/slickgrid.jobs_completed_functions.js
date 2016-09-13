var jobs_completed_grid = null;






$(function() {
        
    var opts = {
        slick_setup: new SlickSetupJobsCompleted,
        gridID : 'contextGrid-completed',
        pageID : 'contextPager-completed',
        search_id_array : ['contextKeywordSearch-completed','contextKeywordSearch-completed2'],
        context_menu_id : 'contextCMenu-completed',
        inline_panel_id : 'inlineFilterPanel-completed'

    };

    jobs_completed_grid = new SlickGridBoilerplate(opts);


});