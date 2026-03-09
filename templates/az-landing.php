<?php
   $programs_json = esc_attr( wp_json_encode( $programs_data ) );
   $config_json   = esc_attr( wp_json_encode( $config_data ) );
   ?>
   <div class="wsu-programs-container"
        data-programs="<?php echo $programs_json; ?>"
        data-config="<?php echo $config_json; ?>">
     <div class="wsu-programs-inner">
       <div class="wsu-header">
         <h1>Degree Programs</h1>
         <p>Explore our comprehensive range of degree programs, certificates, and credentials. Find your path to success at WSU.</p>
       </div>
       <div class="wsu-content-wrapper">
         <div class="wsu-sidebar" id="wsuSidebar">
           <button class="wsu-mobile-filter-toggle" id="wsuMobileFilterToggle" type="button" aria-expanded="false">
             <span>Search & Filters</span>
             <span class="wsu-mobile-toggle-icon" aria-hidden="true">+</span>
           </button>
           <div class="wsu-mobile-filters-content" id="wsuMobileFiltersContent">
             <div class="wsu-search-box">
               <input type="text" id="wsuSearchInput" placeholder="Search programs..." aria-label="Search programs" />
             </div>
             <div class="wsu-filter-section">
               <span class="wsu-filter-label">Filter by Type</span>
               <div class="wsu-degree-type-filters" id="wsuDegreeTypeFilters"><!-- Rendered by JS --></div>
             </div>
           </div>
         </div>
         <main class="wsu-main-content" id="wsuMainContent">
           <div class="wsu-list-controls">
             <div class="wsu-results-count" id="wsuResultsCount">Loading...</div>
             <div class="wsu-expand-collapse-btns">
               <button class="wsu-control-btn" id="wsuExpandAllBtn" aria-label="Expand all groups">Expand All</button>
               <button class="wsu-control-btn" id="wsuCollapseAllBtn" aria-label="Collapse all groups">Collapse All</button>
             </div>
           </div>
           <div class="wsu-programs-list" id="wsuProgramsList"></div>
           <div class="wsu-empty-state" id="wsuEmptyState" style="display: none;">
             <p>No programs found matching your criteria.</p>
             <p style="font-size: 0.9rem; color: #999; margin-top: 10px;">Try adjusting your search or filters.</p>
           </div>
         </main>
         <nav class="wsu-alpha-nav" id="wsuAlphaNav"><!-- Rendered by JS --></nav>
       </div>
     </div>
   </div>