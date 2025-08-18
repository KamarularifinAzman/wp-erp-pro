<?php
namespace WeDevs\Attendance\Queries;

class AttendanceByDate {

    private $wpdb;

    private $date;

    private $attendance_log;

    private $attendance_date_shift;

    private $employee_with_department;

    public function __construct( $date = null ) {
        global $wpdb;

        $this->wpdb = $wpdb;
        $this->date = $date ? $date : date('Y-m-d');

        $this->get_attendance_date_shift();
        $this->get_attendance_log();
        $this->get_employee_with_department();
    }

    /**
     * Get date_shift_id, user_id, shift_name, present/absent
     *
     * @return array
     */
    private function get_attendance_date_shift() {
        $sql = "SELECT ds.id dshift_id,
                ds.user_id, shift.name AS shift_name,
                IF(ds.present is null, 'no', 'yes') AS status

                FROM {$this->wpdb->prefix}erp_attendance_date_shift AS ds
                INNER JOIN {$this->wpdb->prefix}erp_attendance_shifts AS shift ON ds.shift_id = shift.id
                WHERE shift.status = 1 AND ds.date = '{$this->date}'";

        $this->attendance_date_shift = $this->wpdb->get_results( $sql, ARRAY_A );
    }

    /**
     * Get user_id, min_checkin & min_checkin_id,
     * max_checkout & max_checkout_id, worktime
     *
     * @return array
     */
    private function get_attendance_log() {
        $sql = "SELECT
                ds.user_id,
                TIME_FORMAT( TIME( min(al.checkin) ), '%H:%i' ) checkin,
                TIME_FORMAT( TIME( max(alc.checkout) ), '%H:%i' ) checkout,
                min(al.id) checkin_id,
                alc.id checkout_id,
                SUM(time) as worktime

                FROM {$this->wpdb->prefix}erp_attendance_log AS al
                INNER JOIN {$this->wpdb->prefix}erp_attendance_date_shift ds ON al.date_shift_id = ds.id
                INNER JOIN (SELECT id, checkout, user_id, date_shift_id FROM {$this->wpdb->prefix}erp_attendance_log ORDER BY id DESC) alc

                ON alc.date_shift_id = al.date_shift_id
                WHERE ds.date = '{$this->date}'

                GROUP BY ds.user_id, alc.checkout";

        $this->attendance_log = $this->wpdb->get_results( $sql, ARRAY_A );
    }

    /**
     * Get user_id, department_title, employee_id, employee_name
     *
     * @return array
     */
    private function get_employee_with_department() {
        $sql = "SELECT ds.user_id, dep.title as dept, emp.employee_id,
                CONCAT_WS(' ', umeta1.meta_value, umeta2.meta_value) AS name

                FROM {$this->wpdb->prefix}erp_attendance_date_shift ds

                INNER JOIN {$this->wpdb->prefix}erp_hr_employees AS emp ON ds.user_id = emp.user_id
                LEFT JOIN {$this->wpdb->prefix}erp_hr_depts AS dep on emp.department = dep.id
                INNER JOIN {$this->wpdb->prefix}usermeta umeta1 ON umeta1.user_id = ds.user_id
                INNER JOIN {$this->wpdb->prefix}usermeta umeta2 ON umeta2.user_id = ds.user_id
                WHERE umeta1.meta_key='first_name' AND umeta2.meta_key='last_name'
                GROUP BY ds.user_id ORDER BY name ASC";

        $this->employee_with_department = $this->wpdb->get_results( $sql, ARRAY_A );
    }

    /**
     * Merge above results into one array
     *
     * @return array
     */
    public function get_data() {
        $results = [];


        foreach ( $this->attendance_date_shift as $key => $value1 ) {
            $mergeble = true;

            foreach ( array_reverse( $this->attendance_log ) as $value2 ) {
                if ( $value1['user_id'] == $value2['user_id'] ) {
                    $results[] = $value1 + $value2;
                    $mergeble = false;
                }
            }

            if ( $mergeble ) {
                $results[] = $value1;
            }

            foreach ( $this->employee_with_department as $value3 ) {
                if ( $value1['user_id'] == $value3['user_id'] ) {
                    $results[ $key ] = array_merge( $results[ $key ], $value1 + $value3 );
                }
            }
        }

        return $results;
    }

}
