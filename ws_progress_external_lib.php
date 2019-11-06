<?php

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot.'/blocks/completion_progress/lib.php');

class ws_progress_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_progress_parameters() {
        return new external_function_parameters(
            [
                'userid'   => new external_value(PARAM_INT, 'User Id', VALUE_REQUIRED),
                'courseid' => new external_value(PARAM_INT, 'Course Id', VALUE_REQUIRED),
            ]
        );
    }

    /**
     *
     * @param $user_id
     * @param $course_id
     * @return array
     * @throws Exception
     */
    public static function get_progress($user_id, $course_id) {
        global $DB;

        $progress = null;

        try {
            $course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);
            $user = $DB->get_record('user', array('id' => $user_id), '*', MUST_EXIST); //Check if the user exists
            $blockinstances = self::get_block_instances($course_id);

            if ($blockinstances) {
                $blockinstance = array_shift($blockinstances);
                $blockinstance_config = unserialize(base64_decode($blockinstance->configdata), [stdClass::class]);
                if (empty($blockinstance_config)) {
                    $blockinstance_config = null;
                }
                $activities = block_completion_progress_get_activities($course_id, $blockinstance_config);
                $submissions = block_completion_progress_student_submissions($course_id, $user_id);
                $completions = block_completion_progress_completions($activities, $user_id, $course,$submissions);

                $progress = block_completion_progress_percentage($activities, $completions);

            }
        } catch (Exception $e) {
            throw $e;
        }

        return [
            'courseid'  => $course_id,
            'userid'    => $user_id,
            'progress'  => $progress,
        ];
    }

    /**
     * Returns description of method result value
     * @return external_single_structure
     */
    public static function get_progress_returns() {
        return new external_single_structure([
            'courseid'  => new external_value(PARAM_INT, 'Course Id'),
            'userid'    => new external_value(PARAM_INT, 'User Id'),
            'progress'  => new external_value(PARAM_INT, 'Progress')
        ]);
    }

    private static function get_block_instances($course_id)
    {
        global $DB;

        $sql = "SELECT bi.id, bi.configdata
                FROM {block_instances} bi
                WHERE bi.blockname = 'completion_progress'
                   AND bi.parentcontextid = :contextid";

        $context = CONTEXT_COURSE::instance($course_id);
        $params = array('contextid' => $context->id);
        $blockinstances = $DB->get_records_sql($sql, $params);

        return $blockinstances;
    }
}
