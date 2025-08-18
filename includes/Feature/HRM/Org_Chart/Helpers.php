<?php
namespace WeDevs\ERP_PRO\Feature\HRM\Org_Chart;

/**
 * Helper class for org chart
 *
 * @since 1.2.1
 */
class Helpers {

    /**
     * Retrieves the complete hierarchy of employees for comapny organogram
     *
     * @since 1.2.1
     *
     * @param int|string $args
     *
     * @return array
     */
    public static function get_employee_hierarchy( $dept_id = null ) {
        global $wpdb;
        $data = [];

        if ( empty( $dept_id ) ) {
            $data = [
                'id'         => 0,
                'name'       => '',
                'title'      => '',
                'lead'       => 0,
                'avatar'     => '',
                'dept_id'    => 0,
                'email'      => '',
                'is_array'   => true,
                'className'  => 'no-content'
            ];

            $depts = $wpdb->get_results(
                "SELECT DISTINCT dept.id, dept.lead
                FROM {$wpdb->prefix}erp_hr_depts AS dept
                LEFT JOIN {$wpdb->prefix}erp_hr_employees AS emp
                ON dept.id = emp.department
                WHERE dept.status = 1
                AND emp.status = 'active'
                AND emp.deleted_at IS NULL"
            );

            foreach ( $depts as $dept ) {
                $data['children'][] = self::sort_employees( $dept->lead, $dept->id );
            }

            $data['children'][] = self::sort_employees( 0 );
        } else if ( -1 == $dept_id ) {
            $data = self::sort_employees( 0 );
        } else {
            $dept_lead = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT dept.lead
                    FROM {$wpdb->prefix}erp_hr_depts AS dept
                    WHERE dept.id = %d",
                    $dept_id
                )
            );

            $data = self::sort_employees( $dept_lead, $dept_id );
        }

        return $data;
    }

    /**
     * Sorts the department-wise employee hierarchy
     * according to the manager of each employee
     *
     * @since 1.2.1
     *
     * @param int|string $emp_id
     * @param int|string $dept_id
     * @param int $depth
     *
     * @return array
     */
    public static function sort_employees( $emp_id, $dept_id = 0, $depth = 1 ) {
        global $wpdb;

        $data = [
            'id'        => 0,
            'name'      => '',
            'title'     => '',
            'lead'      => 0,
            'avatar'    => '',
            'dept_id'   => $dept_id,
            'email'     => '',
            'className' => '',
        ];

        $sql = [
            'where' => [
                "department = {$dept_id}"
            ]
        ];

        if ( ! (int) $emp_id ) {
            $data['className'] .= ' no-content';

            if ( 1 === $depth ) {
                $sql['where'][] = "(
                    reporting_to = 0
                    OR reporting_to IS NULL
                    OR reporting_to NOT IN (
                        SELECT emp.user_id
                        FROM {$wpdb->prefix}erp_hr_employees AS emp
                        WHERE emp.department = {$dept_id}
                        AND emp.status = 'active'
                        AND emp.deleted_at IS NULL
                    )
                )";

                $data['className'] .= ' no-parent';
            }
        } else {
            if ( 1 === $depth ) {
                $sql['where'][] = "( reporting_to = {$emp_id} OR reporting_to = 0 OR reporting_to IS NULL )";
                $sql['where'][] = "user_id != {$emp_id}";
            } else {
                $sql['where'][] = "reporting_to = {$emp_id}";
            }

            $manager = new \WeDevs\ERP\HRM\Employee( $emp_id );

            if ( ! empty( (int) $manager->get_user_id() ) ) {
                $data['id']     = (int) $manager->get_user_id();
                $data['name']   = $manager->get_full_name();
                $data['title']  = $manager->get_job_title();
                $data['lead']   = (int) $manager->get_reporting_to();
                $data['avatar'] = $manager->get_avatar();
                $data['email']  = $manager->user_email;
            }
        }

        $sql['where'][] = "status = 'active'";
        $sql['where'][] = "deleted_at IS NULL";

        if ( ! empty( $sql['where'] ) ) {
            $where = implode( ' AND ', $sql['where'] );

            $query = "SELECT `user_id`
                    FROM {$wpdb->prefix}erp_hr_employees
                    WHERE {$where}";

            $emp_ids = $wpdb->get_col( $query );

            foreach ( $emp_ids as $index => $id ) {
                $data['children'][ $index ] = self::sort_employees( $id, $dept_id, $depth + 1 );

                if ( empty( (int) $emp_id ) ) {
                    $data['children'][ $index ]['className'] = 'no-parent';
                }
            }
        }

        return $data;
    }

    /**
     * Returns raw dropdown data of departments
     *
     * @since 1.2.1
     *
     * @return array
     */
    public static function get_dept_dropdown_raw() {
        global $wpdb;

        $depts    = $wpdb->get_results(
                        "SELECT DISTINCT dept.id, dept.title
                        FROM {$wpdb->prefix}erp_hr_depts AS dept
                        LEFT JOIN {$wpdb->prefix}erp_hr_employees AS emp
                        ON dept.id = emp.department
                        WHERE dept.status = 1
                        AND emp.status = 'active'
                        AND emp.deleted_at IS NULL"
                    );

        $dropdown = [ '' => __( 'All Teams', 'erp-pro' ) ];

        foreach ( $depts as $index => $dept ) {
            $dropdown[ $dept->id ] = stripslashes( $dept->title ) . __( ' Team', 'erp' );
        }

        $empty_dept = $wpdb->get_row(
                        "SELECT id
                        FROM {$wpdb->prefix}erp_hr_employees
                        WHERE department = 0"
                    );

        if ( $empty_dept ) {
            $dropdown['-1'] = __( 'No Team', 'erp-pro' );
        }

        return $dropdown;
    }
}