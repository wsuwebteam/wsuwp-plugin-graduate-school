

        <div class="factsheet-apply">    
            <?php if ( ! empty( $factsheet_data['application_url'] ) ) : ?>
                <a class="wsu-button" href="<?php echo esc_url( $factsheet_data['application_url'] ); ?>">Apply Now</a>
            <?php endif; ?>

            <?php if (empty( $factsheet_data['application_url'] ) ) : ?>
                <a class="wsu-button" href="https://gradschool.wsu.edu/apply/">Apply Now</a>
            <?php endif; ?>   
        </div>

        <div class="factsheet-statistics-wrapper">
            <div class="factsheet-stat">
                <span class="factsheet-label">Program Link:</span>
                <span class="factsheet-value"><a href="<?php echo esc_url( $factsheet_data['degree_url'] ); ?>"><?php echo esc_html( $factsheet_data['degree_url'] ); ?></a></span>
            </div>
            
            <?php if ( ! empty( $factsheet_data['handbook_url'] ) ) : ?>
                <div class="factsheet-stat">
                    <span class="factsheet-label">Program Handbook:</span>
                    <span class="factsheet-value"><a href="<?php echo esc_url( $factsheet_data['handbook_url'] ); ?>"><?php echo esc_html( $factsheet_data['handbook_url'] ); ?></a></span>
                </div>
            <?php endif; ?>

            <div class="factsheet-stat">
                <span class="factsheet-label">Total Graduate Faculty in Program: </span>
                <span class="factsheet-value"><?php echo absint( $factsheet_data['totalfac'] ); ?></span>
            </div>

            <?php if ( ! empty( $factsheet_data['totalcorefac'] ) ) : ?>
                <div class="factsheet-stat">
                <span class="factsheet-label">Total Core Graduate Faculty in Program: </span>
                <span class="factsheet-value"><?php echo absint( $factsheet_data['totalcorefac'] ); ?></span>
            </div>
            <?php endif; ?>

            <div class="factsheet-stat">
                <span class="factsheet-label">Graduate Students in Program:</span>
                <span class="factsheet-value"><?php echo absint( $factsheet_data['students'] ); ?></span>
            </div>

            <div class="factsheet-stat">
                <span class="factsheet-label">Students receiving assistantships:</span>
                <span class="factsheet-value"><?php echo esc_html( $factsheet_data['aided'] ); ?></span>
            </div>

            <div class="factsheet-stat">
                <span class="factsheet-label">Priority deadline:</span>
                <div class="factsheet-set">
                    <ul class="wsu-list--style-lined">
                        <?php
                        foreach ( $factsheet_data['deadlines'] as $fs_deadline ) {
                            if ( 'NULL' === $fs_deadline['semester'] || 'None' === $fs_deadline['semester'] ) {
                                continue;
                            }

                            if ( in_array( strtolower( $fs_deadline['semester'] ), array( 'summer', 'fall' ), true ) && 'default' === strtolower( $fs_deadline['deadline'] ) ) {
                                $fs_deadline['deadline'] = 'January 10';
                            } elseif ( 'spring' === strtolower( $fs_deadline['semester'] ) && 'default' === strtolower( $fs_deadline['deadline'] ) ) {
                                $fs_deadline['deadline'] = 'July 1';
                            }

                            $is_date_deadline = explode( '/', $fs_deadline['deadline'] );
                            if ( 3 === count( $is_date_deadline ) ) {
                                $date_deadline = strtotime( $fs_deadline['deadline'] );
                                $fs_deadline['deadline'] = date( 'F j', $date_deadline );
                            }

                            echo '<li>' . esc_html( $fs_deadline['semester'] ) . ' ' . esc_html( $fs_deadline['deadline'] ) . ' ' . esc_html( $fs_deadline['international'] ) . '</li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>

            <div class="factsheet-stat">
                <span class="factsheet-label">Campus:</span>
                <div class="factsheet-set">
                    <ul>
                        <?php
                        foreach ( $factsheet_data['locations'] as $fs_location => $fs_location_status ) {
                            if ( 'No' === $fs_location_status || 'By Exception' === $fs_location_status ) {
                                continue;
                            }
                            echo '<li>' . esc_html( $fs_location )  . '</li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>

            <div class="factsheet-stat">
                <span class="factsheet-label">International Student English Proficiency Exams</span>
                <div class="factsheet-set">
                    <ul>
                        <?php
                        foreach ( $factsheet_data['requirements'] as $fs_requirement ) {
                            echo '<li>' . esc_html( $fs_requirement['score'] ) . ' ' . esc_html( $fs_requirement['test'] ) . ' ' . esc_html( $fs_requirement['description'] ) . '</li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>

<div class="wsu-row wsu-row--sidebar-right ">

    <div class="wsu-column">
        <?php if ( ! empty( $factsheet_data['description'] ) ) : ?>
            <div class="factsheet-description">
                <h2>Degree Description:</h2>
                <?php echo wp_kses_post( apply_filters( 'the_content', $factsheet_data['description'] ) ); ?>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $factsheet_data['admission_requirements'] ) ) : ?>
            <div class="factsheet-admission-requirements">
                <h2>Admission Requirements:</h2>
                <?php echo wp_kses_post( apply_filters( 'the_content', $factsheet_data['admission_requirements'] ) ); ?>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $factsheet_data['student_learning_outcome'] ) ) : ?>
            <div class="factsheet-student-learning-outcome">
                <h2>Student Learning Outcomes:</h2>
                <?php echo wp_kses_post( apply_filters( 'the_content', $factsheet_data['student_learning_outcome'] ) ); ?>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $factsheet_data['student_opportunities'] ) ) : ?>
            <div class="factsheet-student-opportunities">
                <h2>Student Opportunities:</h2>
                <?php echo wp_kses_post( apply_filters( 'the_content', $factsheet_data['student_opportunities'] ) ); ?>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $factsheet_data['career_opportunities'] ) ) : ?>
            <div class="factsheet-career-opportunities">
                <h2>Career Opportunities:</h2>
                <?php echo wp_kses_post( apply_filters( 'the_content', $factsheet_data['career_opportunities'] ) ); ?>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $factsheet_data['career_placements'] ) ) : ?>
            <div class="factsheet-career-placements">
                <h2>Career Placements:</h2>
                <?php echo wp_kses_post( apply_filters( 'the_content', $factsheet_data['career_placements'] ) ); ?>
            </div>
        <?php endif; ?>
        </div>
    <div class="wsu-column">
        <h2>Contact Information:</h2>
        <?php
        foreach ( $factsheet_data['contacts'] as $contact ) {
            ?>
            <address class="factsheet-contact" itemscope itemtype="http://schema.org/Organization">
                <?php if ( ! empty( $contact['gs_contact_name'][0] ) ) : ?>
                <div itemprop="contactPoint" itemscope itemtype="http://schema.org/Person"><?php echo esc_html( $contact['gs_contact_name'][0] ); ?></div>
                <?php endif; ?>
                <div class="address">
                    <?php if ( ! empty( $contact['gs_contact_address_one'][0] ) ) : ?>
                    <div itemprop="streetAddress"><?php echo esc_html( $contact['gs_contact_address_one'][0] ); ?></div>
                    <?php endif; ?>
                    <?php if ( ! empty( $contact['gs_contact_address_two'][0] ) ) : ?>
                    <div itemprop="streetAddress"><?php echo esc_html( $contact['gs_contact_address_two'][0] ); ?></div>
                    <?php endif; ?>
                    <div>
                        <?php if ( ! empty( $contact['gs_contact_city'][0] ) && ! empty( $contact['gs_contact_state'][0] ) ) : ?>
                        <span itemprop="addressLocality"><?php echo esc_html( $contact['gs_contact_city'][0] ); ?>, <?php echo esc_html( $contact['gs_contact_state'][0] ); ?></span>
                        <?php endif; ?>
                        <?php if ( ! empty( $contact['gs_contact_postal'][0] ) ) : ?>
                        <span itemprop="postalcode"><?php echo esc_html( $contact['gs_contact_postal'][0] ); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ( ! empty( $contact['gs_contact_phone'][0] ) ) : ?>
                <div itemprop="telephone"><?php echo esc_html( $contact['gs_contact_phone'][0] ); ?></div>
                <?php endif; ?>
                <?php if ( ! empty( $contact['gs_contact_fax'][0] ) ) : ?>
                <div itemprop="faxNumber"><?php echo esc_html( $contact['gs_contact_fax'][0] ); ?></div>
                <?php endif; ?>
                <?php if ( ! empty( $contact['gs_contact_email'][0] ) ) : ?>
                <div itemprop="email"><a href="mailto:<?php echo esc_attr( $contact['gs_contact_email'][0] ); ?>"><?php echo esc_html( $contact['gs_contact_email'][0] ); ?></a></div>
                <?php endif; ?>
            </address>
            <?php
        }
        ?>
    </div>
</div>
<div class="wsu-row">
    <div class="wsu-column">
        <div class="progressbar"></div>
        <div class="footer">
        </div>
    </div>
    </div>