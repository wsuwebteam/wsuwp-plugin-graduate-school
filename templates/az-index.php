
<!-- <form class="degree-search-wrapper">
    <label for="degree-search-input" class="visuallyhidden">Search Degrees</label>
    <input type="text" name="search-degrees" id="degree-search-input" placeholder="Search Degrees A-Z" />
    <button type="submit">Search</button>
</form> -->

<div class="degree-list">
    <div class="toparea">
        <div class="pagination">
            <a class="active" href="#a">A</a> <a href="#b">B</a> <a href="#c">C</a> <a href="#d">D</a> <a href="#e">E</a> <a href="#f">F</a> <a href="#g">G</a> <a href="#h">H</a> <a href="#i">I</a> <a href="#j">J</a> <a href="#k">K</a> <a href="#l">L</a> <a href="#m">M</a> <a href="#n">N</a> <a href="#o">O</a> <a href="#p">P</a> <a href="#q">Q</a> <a href="#r">R</a> <a href="#s">S</a> <a href="#t">T</a> <a href="#u">U</a> <a href="#v">V</a> <a href="#w">W</a> <a href="#x">X</a> <a href="#y">Y</a> <a href="#z">Z</a>
        </div>

        <div class="filter-helper-text">
            <p>Click on any degree type below to filter the list.</p>
        </div>

        <div class="key-group">
            <div class="key-classification">
                <span>Doctoral Degrees</span>
                <div class="degree-classification doctorate">D</div>
            </div>

            <div class="key-classification">
                <span>Master's Degrees</span>
                <div class="degree-classification masters">M</div>
            </div>

            <div class="key-classification">
                <span>Professional Masters</span>
                <div class="degree-classification professional-masters">PM</div>
            </div>

            <div class="key-classification">
                <span>4+1 Master's Entry</span>
                <div class="degree-classification masters-4plus1">4+1</div>
            </div>

            <div class="key-classification">
                <span>Graduate Certificates</span>
                <div class="degree-classification graduate-certificate">GC</div>
            </div>

            <div class="key-classification">
                <span>Administrator Credentials</span>
                <div class="degree-classification administrator-credentials">C</div>
            </div>

        </div>
    </div>

    <div class="lettergroup">
        <a id="a" name="a"></a>
        <div class="bigletter active">A</div>
        <div class="bigletterline"></div>
        <ul>
        <?php
        $letter = 'a';
        foreach ( $factsheets as $factsheet_name => $factsheet ) {
            $factsheet_character = trim( substr( $factsheet_name, 0, 1 ) );

            // Avoid indefinite loops by skipping factsheets that don't start with a-z.
            if ( ! preg_match( '/^[a-zA-Z]$/', $factsheet_character ) ) {
                continue;
            }

            // Output the letter separators between sets of factsheets.
            while ( 0 !== strcasecmp( $factsheet_character, $letter ) ) {
                echo '</ul></div>';

                // It's funny and sad, but this works. a becomes b, z becomes aa.
                $letter++;
                ?>
                <div class="lettergroup">
                    <a id="<?php echo esc_attr( $letter ); ?>" name="<?php echo esc_attr( $letter ); ?>"></a>
                    <div class="bigletter active"><?php echo strtoupper( $letter ); // @codingStandardsIgnoreLine (This is a controlled letter) ?></div>
                    <div class="bigletterline"></div>
                    <ul>
                <?php
            }

            if ( 1 < count( $factsheet ) ) {
                $wrapper_class = 'degree-row-multiple';
            } else {
                $wrapper_class = 'degree-row-single';
            }
            ?>
            <li class="degree-row-wrapper <?php echo esc_attr( $wrapper_class ); ?>">
                <div class="degree-row-top">
                    <?php
                    if ( 1 < count( $factsheet ) ) {
                        ?><div class="degree-name"><span class="degree-caret">▶</span><span class="degree-anchor"><?php echo esc_html( $factsheet_name ); ?></span><?php
                    } else {
                        ?><div class="degree-name"><span class="degree-link-icon">🔗</span><a href="<?php echo esc_url( $factsheet[0]['permalink'] ); ?>"><?php echo esc_html( $factsheet_name ); ?></a><?php
                    }
                    ?>
                    </div>
                    <?php
                    // Collect all badges first, then sort them
                    $all_badges = array();
                    foreach ( $factsheet as $item ) {
                        // Check if this is a grouped masters degree
                        if ( isset( $item['degree_classifications'] ) && is_array( $item['degree_classifications'] ) ) {
                            // Add all classifications from grouped masters
                            foreach ( $item['degree_classifications'] as $classification ) {
                                $all_badges[] = $classification;
                            }
                        } else {
                            // Single badge for non-grouped degrees
                            $all_badges[] = $item['degree_classification'];
                        }
                    }
                    
                    // Sort badges in the specified order
                    $badge_order = array(
                        'doctorate',
                        'masters',
                        'professional-masters',
                        'masters-4plus1',
                        'graduate-certificate',
                        'administrator-credentials',
                    );
                    
                    usort( $all_badges, function( $a, $b ) use ( $badge_order ) {
                        $pos_a = array_search( $a, $badge_order );
                        $pos_b = array_search( $b, $badge_order );
                        
                        // If not found in order array, put at end
                        if ( false === $pos_a ) {
                            $pos_a = 999;
                        }
                        if ( false === $pos_b ) {
                            $pos_b = 999;
                        }
                        
                        return $pos_a - $pos_b;
                    } );
                    
                    // Display sorted badges
                    foreach ( $all_badges as $classification ) {
                                ?>
                                <div class="degree-classification <?php echo esc_attr( $classification ); ?>">
                                    <?php
                                    // Output the appropriate abbreviation for the degree classification.
                                    if ( 'graduate-certificate' === $classification ) {
                                        echo 'GC';
                                    } elseif ( 'administrator-credentials' === $classification ) {
                                        echo 'C';
                                    } elseif ( 'professional-masters' === $classification ) {
                                        echo 'PM';
                                    } elseif ( 'masters-4plus1' === $classification ) {
                                        echo '4+1';
                                    } elseif ( 'masters' === $classification ) {
                                        echo 'M';
                                    } else {
                                        echo esc_html( $classification[0] );
                                    }
                                    ?>
                                </div>
                                <?php
                    }
                    ?>
                </div>

                <?php
                foreach ( $factsheet as $item ) {
                    // Check if this is a grouped masters degree
                    if ( isset( $item['degree_classifications'] ) && is_array( $item['degree_classifications'] ) ) {
                        // Create a single grouped masters row with all badges
                        ?>
                        <div class="degree-row-bottom">
                            <div class="degree-detail">
                                <?php
                                if ( ! empty( $item['degree_type'] ) ) {
                                    echo '<a href="' . esc_url( $item['permalink'] ) . '">' . esc_html( $item['degree_type'] ) . '</a>';
                                }
                                ?>
                            </div>
                            <?php
                            // Sort and display all masters badges
                            $sorted_classifications = $item['degree_classifications'];
                            $badge_order = array(
                                'doctorate',
                                'masters',
                                'professional-masters',
                                'masters-4plus1',
                                'graduate-certificate',
                                'administrator-credentials',
                            );
                            
                            usort( $sorted_classifications, function( $a, $b ) use ( $badge_order ) {
                                $pos_a = array_search( $a, $badge_order );
                                $pos_b = array_search( $b, $badge_order );
                                
                                // If not found in order array, put at end
                                if ( false === $pos_a ) {
                                    $pos_a = 999;
                                }
                                if ( false === $pos_b ) {
                                    $pos_b = 999;
                                }
                                
                                return $pos_a - $pos_b;
                            } );
                            
                            foreach ( $sorted_classifications as $classification ) {
                                ?>
                                <div class="degree-classification <?php echo esc_attr( $classification ); ?>">
                                    <?php
                                    // Output the appropriate abbreviation for the degree classification.
                                    if ( 'graduate-certificate' === $classification ) {
                                        echo 'GC';
                                    } elseif ( 'administrator-credentials' === $classification ) {
                                        echo 'C';
                                    } elseif ( 'professional-masters' === $classification ) {
                                        echo 'PM';
                                    } elseif ( 'masters-4plus1' === $classification ) {
                                        echo '4+1';
                                    } elseif ( 'masters' === $classification ) {
                                        echo 'M';
                                    } else {
                                        echo esc_html( $classification[0] );
                                    }
                                    ?>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    } else {
                        // Single degree type for non-grouped degrees
                        ?>
                        <div class="degree-row-bottom">
                            <div class="degree-detail">
                                <?php
                                if ( ! empty( $item['degree_type'] ) ) {
                                    echo '<a href="' . esc_url( $item['permalink'] ) . '">' . esc_html( $item['degree_type'] ) . '</a>';
                                }
                                ?>
                            </div>
                            <div class="degree-classification <?php echo esc_attr( $item['degree_classification'] ); ?>">
                                <?php
                                // Output the appropriate abbreviation for the degree classification.
                                if ( 'graduate-certificate' === $item['degree_classification'] ) {
                                    echo 'GC';
                                } elseif ( 'administrator-credentials' === $item['degree_classification'] ) {
                                    echo 'C';
                                } elseif ( 'professional-masters' === $item['degree_classification'] ) {
                                    echo 'PM';
                                } elseif ( 'masters-4plus1' === $item['degree_classification'] ) {
                                    echo '4+1';
                                } else {
                                    echo esc_html( $item['degree_classification'][0] );
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </li>
            <?php
        }
        ?>
        </ul>
    </div>
    <?php
    $letter++;

    while ( 'aa' !== $letter ) {
        ?>
        <div class="lettergroup">
            <a id="<?php echo esc_attr( $letter ); ?>" name="<?php echo esc_attr( $letter ); ?>"></a>
            <div class="bigletter active"><?php echo strtoupper( $letter ); // @codingStandardsIgnoreLine (This is a controlled letter) ?></div>
            <div class="bigletterline"></div>
        </div>
        <?php
        $letter++;
    }
    ?>
</div>


