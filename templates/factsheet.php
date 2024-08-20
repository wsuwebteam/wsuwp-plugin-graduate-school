
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

            <?php if ( ! empty( $factsheet_data['student_learning_outcome_url'] ) ) : ?>
                <div class="factsheet-stat">
                    <span class="factsheet-label">Student Learning Outcomes:</span>
                    <span class="factsheet-value"><a href="<?php echo esc_url( $factsheet_data['student_learning_outcome_url'] ); ?>"><?php echo esc_html( $factsheet_data['student_learning_outcome_url'] ); ?></a></span>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $factsheet_data['totalfac'] ) ) : ?>

                <div class="factsheet-stat">
                    <span class="factsheet-label">Total Graduate Faculty in Program: </span>
                    <span class="factsheet-value"><?php echo absint( $factsheet_data['totalfac'] ); ?></span>
                </div>

            <?php endif; ?>


            <?php if ( ! empty( $factsheet_data['totalcorefac'] ) ) : ?>
                <div class="factsheet-stat">
                <span class="factsheet-label">Total Core Graduate Faculty in Program: </span>
                <span class="factsheet-value"><?php echo absint( $factsheet_data['totalcorefac'] ); ?></span>
            </div>
            <?php endif; ?>

            <?php if ( ! empty( $factsheet_data['students'] ) ) : ?>

                <div class="factsheet-stat">
                    <span class="factsheet-label">Graduate Students in Program:</span>
                    <span class="factsheet-value"><?php echo absint( $factsheet_data['students'] ); ?></span>
                </div>
           <?php endif; ?>

            <?php if ( !empty( $factsheet_data['aided']) && !empty( $factsheet_data['students']))   : ?>

                <div class="factsheet-stat">
                    <span class="factsheet-label">Students Receiving Assistantships: </span>
                    <span class="factsheet-value"><?php echo esc_html( $factsheet_data['aided'] ); ?></span>
                </div>

            <?php endif; ?>


            <div class="factsheet-stat">
                <span class="factsheet-label">Priority Deadlines:</span>
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
    
            <?php if(!empty((($factsheet_data['deadlines_prog'])[0])["deadline"])):?>

            <div class="factsheet-stat">
                <span class="factsheet-label">Program Deadlines:</span>
                <div class="factsheet-set">
                    <ul class="wsu-list--style-lined">
                        <?php
                        foreach ( $factsheet_data['deadlines_prog'] as $fs_deadline_prog ) {
                            if ( 'NULL' === $fs_deadline_prog['semester'] || 'None' === $fs_deadline_prog['semester'] ) {
                                continue;
                            }

                            if ( in_array( strtolower( $fs_deadline_prog['semester'] ), array( 'summer', 'fall' ), true ) && 'default' === strtolower( $fs_deadline_prog['deadline'] ) ) {
                                $fs_deadline_prog['deadline'] = 'January 10';
                            } elseif ( 'spring' === strtolower( $fs_deadline_prog['semester'] ) && 'default' === strtolower( $fs_deadline_prog['deadline'] ) ) {
                                $fs_deadline_prog['deadline'] = 'July 1';
                            }

                            $is_date_deadline = explode( '/', $fs_deadline_prog['deadline'] );
                            if ( 3 === count( $is_date_deadline ) ) {
                                $date_deadline = strtotime( $fs_deadline_prog['deadline'] );
                                $fs_deadline_prog['deadline'] = date( 'F j', $date_deadline );
                            }

                            echo '<li>' . esc_html( $fs_deadline_prog['semester'] ) . ' ' . esc_html( $fs_deadline_prog['deadline'] ) . ' ' . esc_html( $fs_deadline_prog['international'] ) . '</li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>

            <?php endif; ?>


            <div class="factsheet-stat">
                <span class="factsheet-label">Campus:</span>
                <div class="factsheet-set">
                    <ul>
                        <?php
                        foreach ( $factsheet_data['locations'] as $fs_location => $fs_location_status ) {
                            if ( 'No' === $fs_location_status || 'By Exception' === $fs_location_status ) 
                            {
                                continue;
                            }
                            if($fs_location == "Global Campus (online)" and !empty($factsheet_data['global_URL'] ))
                            {
                                echo '<li><a target="_blank" href="' . esc_html($factsheet_data['global_URL']) . '">Global Campus (online)</a></li>';
                            }
                            else
                            {
                                echo '<li>' . esc_html( $fs_location )  . '</li>';
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div>

                <div class="factsheet-stat">
                    <span class="factsheet-label">International Student English Proficiency Exams</span>
                    <div class="factsheet-set">
                   <p class="font-factsheet-label" style="padding-left:25px;">International students may need to surpass the Graduate School's minimum English language proficiency exam scores for this program. If the graduate program has unique score requirements, they will be detailed below. Otherwise, please refer to <a href="https://gradschool.wsu.edu/international-requirements/">the Graduate School's minimum score guidelines. </a></p>
                    <?php if(!empty((($factsheet_data['requirements'])[0])["score"])):?>
    
                        <ul>
                            <?php
                            foreach ( $factsheet_data['requirements'] as $fs_requirement ) {
                                echo '<li>' . esc_html( $fs_requirement['score'] ) . ' ' . esc_html( $fs_requirement['test'] ) . ' ' . esc_html( $fs_requirement['description'] ) . '</li>';
                            }
                            ?>
                        </ul>

                    <?php endif; ?>

                    </div>
                </div>



            <?php if(!empty((($factsheet_data['requirements_gre'])[0])["test"])):?>
                <div class="factsheet-stat">
                    <span class="factsheet-label">Additional Degree Program Admission Requirements</span>
                    <div class="factsheet-set">
                        
                        <ul>
                            <?php
                            foreach ( $factsheet_data['requirements_gre'] as $fs_requirement_gre ) {
                                echo '<li>' . esc_html( $fs_requirement_gre['test'] ) . ' ' . esc_html( $fs_requirement_gre['required'] ) . '</li>';
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>           


       
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
        <ul>
            <?php
                    foreach ( $factsheet_data['gscontacts'] as $fs_contact ) {
                                echo '<li>' . esc_html( $fs_contact['name'] ) . ' <a href="mailto:' . esc_html( $fs_contact['email'] ) .'">'. esc_html( $fs_contact['email'] ). '</a></li>';
                    }
            ?>
        </ul>

    </div>
</div>


