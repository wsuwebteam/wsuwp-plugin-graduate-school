
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
        <div class="key-group">
            <div class="key-classification">
                <span>Graduate Certificate</span>
                <div class="degree-classification graduate-certificate">GC</div>
            </div>
            <div class="key-classification">
                <span>Doctorate</span>
                <div class="degree-classification doctorate">D</div>
            </div>
            <div class="key-classification">
                <span>Master</span>
                <div class="degree-classification masters">M</div>
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
                        ?><div class="degree-name"><span class="degree-anchor"><?php echo esc_html( $factsheet_name ); ?></span><?php
                    } else {
                        ?><div class="degree-name"><a href="<?php echo esc_url( $factsheet[0]['permalink'] ); ?>"><?php echo esc_html( $factsheet_name ); ?></a><?php
                    }
                    ?>
                    </div>
                    <?php
                    foreach ( $factsheet as $item ) {
                        ?>
                        <div class="degree-classification <?php echo esc_attr( $item['degree_classification'] ); ?>">
                            <?php
                            // Output the first character of the degree classification string.
                            if ( 'graduate-certificate' === $item['degree_classification'] ) {
                                echo 'GC';
                            } else {
                                echo esc_html( $item['degree_classification'][0] );
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <?php
                foreach ( $factsheet as $item ) {
                    ?>
                    <div class="degree-row-bottom">
                        <div class="degree-detail">
                                <?php
                                echo '<a href="' . esc_url( $item['permalink'] ) . '">' . esc_html( $factsheet_name ) . '</a>';

                                if ( ! empty( $item['degree_type'] ) ) {
                                    echo ' | ' . esc_html( $item['degree_type'] );
                                }

                                if ( ! empty( $item['program_name'] ) ) {
                                    echo ' | ' . esc_html( $item['program_name'] );
                                }
                                ?>
                        </div>
                        <div class="degree-classification <?php echo esc_attr( $item['degree_classification'] ); ?>">
                            <?php
                            // Output the first character of the degree classification string.
                            if ( 'graduate-certificate' === $item['degree_classification'] ) {
                                echo 'GC';
                            } else {
                                echo esc_html( $item['degree_classification'][0] );
                            }
                            ?>
                        </div>
                    </div>
                    <?php
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


