<?php
$route = get_route();

// make sure any ajax call to this script receive status 200
header('HTTP/1.1 200 OK');

if (!isset($route[1])) :
    ?>
    <!DOCTYPE html>
    <html><head></head></html>
<?php endif ?>
<?php
global $wpdb;
$task = $route[1];
if (isset($route[2])) {
    $do = $route[2];
}

/*
 * ajax search for dictionary
 */
if ($task == 'dictionary') {
    $d = $_GET['d'];
    $dict_table = get_dictionary_table($d);

    $words = $wpdb->get_results($wpdb->prepare(
                    'SELECT DISTINCT entry FROM ' . $wpdb->prefix . $dict_table . ' WHERE entry LIKE %s LIMIT 0, 8', array($_GET['w'] . '%')
    ));

    // user might input inflected form. Try to get original form
    if (empty($words)) {
        $search = $wpdb->get_row($wpdb->prepare('SELECT DISTINCT entry FROM ' . $wpdb->prefix . $dict_table . ' WHERE REPLACE(inflection, \'*\', \'\') LIKE %s', array('%<if>' . $_GET['w'] . '</if>%')));
    }

    // research
    if (!is_null($search)) {
        $words = $wpdb->get_results($wpdb->prepare(
                        'SELECT DISTINCT entry FROM ' . $wpdb->prefix . $dict_table . ' WHERE entry LIKE %s LIMIT 0, 8', array($search->entry . '%')
        ));
    }

    if (!empty($words)) {
        foreach ($words as $word) {
            ?><a href="<?php echo locale_home_url() . '/?r=dictionary/' . $d . '/' . $word->entry ?>"><?php echo $word->entry ?></a><?php
        }
    } else {
        $words = $wpdb->get_results($wpdb->prepare(
                        'SELECT DISTINCT entry, levenshtein(entry, %s) AS lev FROM `wp_dict_elementary` WHERE entry LIKE %s ORDER BY lev LIMIT 8', array($_GET['w'], substr($_GET['w'], 0, 2) . '%')
        ));

        foreach ($words as $word) {
            ?><a href="<?php echo locale_home_url() . '/?r=dictionary/' . $d . '/' . $word->entry ?>"><?php echo $word->entry ?></a><?php
        }
    }

    exit;
}

/*
 * return a random quiz
 */
if ($task == 'randomquiz') {
    $dictionary = $_GET['d'];
    $sheet_category = $_GET['c'];

    echo json_encode(MWDB::random_quiz($dictionary, $sheet_category));

    die;
}

/*
 * remove an item from search history
 */
if ($task == 'history') {
    if ($do == 'remove') {
        remove_search_history_item($_REAL_POST['id'], $_REAL_POST['d']);
    }
}

/*
 * return sheet content
 */
if ($task == 'sheets') {
    $sid = $_GET['sid'];

    if (isset($_GET['readonly'])) {
        if ($_GET['readonly'])
            $readonly = ' readonly="readonly"';
        else
            $readonly = '';
    } else
        $readonly = '';

    $result = $wpdb->get_row($wpdb->prepare(
                    'SELECT * FROM ' . $wpdb->prefix . 'dict_sheets WHERE id = %d', array($sid)
    ));

    if (is_null($result)) {
        die('0');
    }
    $questions = json_decode($result->questions, true);

    $html = '<tbody>';
    switch ($result->assignment_id) {
        case ASSIGNMENT_SPELLING:
            for ($i = 1; $i <= 20; $i++) {
                $html .= '<tr>';
                $html .= '<td><input type="text" value="' . esc_html($questions[$i - 1]) . '"' . $readonly . '></td>';
                $html .= '</tr>';
            }
            break;
        case ASSIGNMENT_VOCAB_GRAMMAR:
            for ($i = 1; $i <= 20; $i++) {
                $html .= '<tr>';
                $html .= '<td><input type="text" value="' . esc_html($questions['question'][$i - 1]) . '"></td>';
                $html .= '<td><input type="text" value="' . esc_html($questions['c_answer'][$i - 1]) . '"' . $readonly . '></td>';
                $html .= '<td><input type="text" value="' . esc_html($questions['w_answer1'][$i - 1]) . '"' . $readonly . '></td>';
                $html .= '<td><input type="text" value="' . esc_html($questions['w_answer2'][$i - 1]) . '"' . $readonly . '></td>';
                $html .= '<td><input type="text" value="' . esc_html($questions['w_answer3'][$i - 1]) . '"' . $readonly . '></td>';
                $html .= '<td><input type="text" value="' . esc_html($questions['w_answer4'][$i - 1]) . '"' . $readonly . '></td>';
                $html .= '<td><input type="text" value="' . esc_html($questions['quiz'][$i - 1]) . '"></td>';
                $html .= '</tr>';
            }
            break;
        case ASSIGNMENT_READING:
            for ($i = 1; $i <= 20; $i++) {
                $html .= '<tr>';
                $html .= '<td><input type="text" value="' . $questions['question'][$i - 1] . '"></td>';
                $html .= '<td><input type="text" value="' . $questions['c_answer'][$i - 1] . '"></td>';
                $html .= '<td><input type="text" value="' . $questions['w_answer1'][$i - 1] . '"></td>';
                $html .= '<td><input type="text" value="' . $questions['w_answer2'][$i - 1] . '"></td>';
                $html .= '<td><input type="text" value="' . $questions['w_answer3'][$i - 1] . '"></td>';
                $html .= '<td><input type="text" value="' . $questions['w_answer4'][$i - 1] . '"></td>';
                $html .= '<td><input type="text" value="' . $questions['quiz'][$i - 1] . '"></td>';
                $html .= '</tr>';
            }
            $json['passage'] = $result->passages;
            break;
        case ASSIGNMENT_WRITING:
            for ($i = 1; $i <= 20; $i++) {
                $html .= '<tr>';
                $html .= '<td><textarea>' . $questions['question'][$i - 1] . '</textarea></td>';
                $html .= '<td><input type="text" value="' . $questions['quiz'][$i - 1] . '"></td>';
                $html .= '</tr>';
            }
            break;
    }
    $html .= '</tbody>';

    $json['html'] = $html;
    // $json['desc'] = $result->description;

    echo json_encode($json);
    die;
}

/*
 * return a question
 */
if ($task == 'question') {
    $current_user_id = get_current_user_id();
    if (isset($_GET['hid']) && is_numeric($_GET['hid'])) {
        $sheet = $wpdb->get_row($wpdb->prepare(
                        'SELECT s.*, hs.id AS result_id, finished_question
				FROM ' . $wpdb->prefix . 'dict_homeworks AS h
				JOIN ' . $wpdb->prefix . 'dict_sheets AS s ON s.id = h.sheet_id
				LEFT JOIN ' . $wpdb->prefix . 'dict_homework_results AS hs ON hs.homework_id = h.id
				WHERE h.id = %d AND (userid = %d OR userid IS NULL)', $_GET['hid'], $current_user_id
        ));

        if (is_null($sheet->result_id)) {
            $json['rid'] = $json['lq'] = 0;
        } else {
            $json['rid'] = $sheet->result_id;
            $json['lq'] = $sheet->finished_question;
        }
    }

    if (isset($_GET['sid']) && is_numeric($_GET['sid'])) {
        $sheet = $wpdb->get_row($wpdb->prepare(
                        'SELECT s.*, p.id AS pid, p.answers AS practice_answers
				FROM ' . $wpdb->prefix . 'dict_sheets AS s
				LEFT JOIN ' . $wpdb->prefix . 'dict_practice_results AS p ON p.sheet_id = s.id AND p.user_id = ' . $current_user_id . '
				WHERE s.id = %s', $_GET['sid']
        ));
    }

    $words = json_decode($sheet->questions);
    $practice_answers = json_decode($sheet->practice_answers, true);
    $dict_table = get_dictionary_table($sheet->dictionary_id);
    include IK_PLUGIN_DIR . '/library/formatter.php';

    if ($sheet->assignment_id == ASSIGNMENT_SPELLING) {
        $insql = '';
        $count = 0;
        foreach ($words as $key => $v) {
            $insql[] = "'" . esc_sql($v) . "'";
        }

        $results = $wpdb->get_results(
                'SELECT id, entry, sound, sound_url, definition 
				FROM ' . $wpdb->prefix . $dict_table . ' 
				WHERE entry IN (' . implode(',', $insql) . ')'
        );

        foreach ($results as $item) {
            $tmp[strtolower($item->entry)][] = array(
                'id' => $item->id,
                'entry' => $item->entry,
                'sound' => $item->sound,
                'sound_url' => $item->sound_url,
                'definition' => $item->definition
            );
        }

        foreach ($tmp as $items) {
            $a = array();

            foreach ($items as $item) {
                $a['entry'] = $item['entry'];
                $a['def'] .= WFormatter::_def($item['definition'], $sheet->dictionary_id);
                if (!isset($a['sound'])) {
                    if (!is_null($item['sound_url'])) {
                        $a['sound'] = $item['sound_url'];
                    } else {
                        $sound_url = WFormatter::_sound($item['sound'], $sheet->dictionary_id, true);
                        $a['sound'] = $sound_url;
                        if ($sound_url != '') {
                            $wpdb->update(
                                    $wpdb->prefix . $dict_table, array(
                                'sound_url' => $sound_url
                                    ), array('id' => $item['id'])
                            );
                        }
                    }
                }
                $ans = '';
                if (isset($practice_answers['q' . $count])) {
                    $ans = $practice_answers['q' . $count];
                }
                $a['selected'] = $ans;
            }
            $json['sheet'][] = $a;
            $count++;
        }
    } else {
        for ($i = 0; $i < count($words->question); $i++) {
            $ans = '';
            if (isset($practice_answers['q' . $i])) {
                $ans = $practice_answers['q' . $i];
            }

            $answers = array(
                array($words->c_answer[$i], 1),
                array($words->w_answer1[$i], 0),
                array($words->w_answer2[$i], 0)
            );
            if (!empty($words->w_answer3[$i])) {
                $answers[] = array($words->w_answer3[$i], 0);
            }
            if (!empty($words->w_answer4[$i])) {
                $answers[] = array($words->w_answer4[$i], 0);
            }

            $q[$i] = array(
                'sentence' => $words->question[$i],
                'answers' => $answers,
                'c_a' => $words->c_answer[$i],
                'quiz' => $words->quiz[$i],
                'selected' => $ans
            );
        }

        if (in_array($sheet->assignment_id, array(ASSIGNMENT_VOCAB_GRAMMAR, ASSIGNMENT_READING)) !== false) {
            $def_js = array();
            $json['sheet'] = array($q, $def_js);
        } else {
            $json['sheet'][] = $q;
        }
    }

    $json['pid'] = is_null($sheet->pid) ? 0 : (int) $sheet->pid;

    if ($sheet->assignment_id == ASSIGNMENT_READING) {
        $json['sheet']['passage'] = $sheet->passages;
    }

    $json['htype'] = '';
    if ($sheet->homework_type_id == HOMEWORK_PUBLIC) {
        $json['htype'] = __('Worksheet - Free', 'iii-dictionary');
    } else if ($sheet->homework_type_id == HOMEWORK_SUBSCRIBED) {
        $json['htype'] = __('Worksheet - Subscribed', 'iii-dictionary');
    }

    echo json_encode($json);
    die;
}

/*
 * saving practice answers
 */
if ($task == 'practice') {
    $userid = get_current_user_id();

    if (!$userid) {
        die;
    }

    if ($do == 'save') {
        $pid = $_REAL_POST['pid'];
        $q = $_REAL_POST['q'];
        $sid = $_REAL_POST['sid'];
        $answers = array('q' . $q => $_REAL_POST['answer']);
        $ptid = $_REAL_POST['ptid'];
        if (!$pid) {
            $result = $wpdb->insert(
                    $wpdb->prefix . 'dict_practice_results', array(
                'user_id' => $userid,
                'sheet_id' => $sid,
                'answers' => json_encode($answers),
                'practice_id' => $ptid
                    )
            );

            $pid = $wpdb->insert_id;
        } else {
            $row = $wpdb->get_row('SELECT answers, practice_id  FROM ' . $wpdb->prefix . 'dict_practice_results WHERE id = ' . esc_sql($pid));
            if ($row) {
                $updated_answers = array_merge(json_decode($row->answers, true), $answers);
                $ptid = $ptid != 0 ? $ptid : $row->practice_id;
                $result = $wpdb->update(
                        $wpdb->prefix . 'dict_practice_results', array(
                    'answers' => json_encode($updated_answers),
                    'practice_id' => $ptid
                        ), array('id' => $pid)
                );
            }
        }

        if ($result !== false) {
            echo json_encode(array($pid));
        } else {
            echo json_encode(array(0));
        }
        exit;
    }
}

/*
 * saving homework answers
 */
if ($task == 'homework') {
    $userid = get_current_user_id();

    if (!$userid || !isset($_POST['homework_id'])) {
        die;
    }
    $valuepoint = 0;
    // saving answers as student progress
    if ($do == 'answer') {
        $q = $_REAL_POST['q'];
        $question_count = $_REAL_POST['qc'];
        $rid = $_REAL_POST['rid'];
        $answer = !empty($_POST['writing']) ? $_REAL_POST['answer'] : json_decode($_REAL_POST['answer'], true);
        $graded = isset($_REAL_POST['graded']) ? $_REAL_POST['graded'] : 1;

        $score = 0;
        $score_per_question = 100 / $question_count;

        if (!$rid) {
            $ca = 0;
            if ($answer->score) {
                $score = $score_per_question;
                $ca = 1;
            }

            $result = $wpdb->insert(
                    $wpdb->prefix . 'dict_homework_results', array(
                'userid' => $userid,
                'homework_id' => $_POST['homework_id'],
                'answers' => json_encode(array('q' . $q => $answer)),
                'score' => $score,
                'correct_answers_count' => $ca,
                'attempted_on' => date('Y-m-d', time()),
                'finished_question' => $q,
                'finished' => 0,
                'graded' => $graded
                    )
            );

            if ($result) {
                echo json_encode(array($wpdb->insert_id));
            } else {
                echo json_encode(array(0));
            }
            exit;
        } else {
            $result_sheet = $wpdb->get_row($wpdb->prepare(
                            'SELECT answers, correct_answers_count, score 
					FROM ' . $wpdb->prefix . 'dict_homework_results 
					WHERE id = %d', $rid
            ));

            $answers = json_decode($result_sheet->answers, true);
            $answers['q' . $q] = $answer;

            $correct_count = 0;
            // check for number of correct answers if this is not writing homework
            if (empty($_POST['writing'])) {
                foreach ($answers as $item) {
                    if ($item['score']) {
                        $correct_count++;
                    }
                }
            }
            // calculate total score
            $score = $correct_count * $score_per_question;

            $result_data = array(
                'answers' => json_encode($answers),
                'correct_answers_count' => $correct_count,
                'score' => $score,
                'finished_question' => $q,
                'attempted_on' => date('Y-m-d', time()),
                'graded' => $graded
            );

            $result = $wpdb->update(
                    $wpdb->prefix . 'dict_homework_results', $result_data, array('id' => $rid)
            );

            if ($result !== false) {
                echo json_encode(array($rid));
            } else {
                echo json_encode(array(0));
            }
            $valuepoint = $score;
            exit;
        }
    }

    // set the homework to finished
    if ($do == 'submit') {

        $rid = esc_sql($_POST['rid']);
        $feedback = esc_sql(stripslashes($_POST['feedback']));
        // If teacher take the test
        if (isset($_POST['teacher_taking_test']) && $_POST['teacher_taking_test'] == 1) {
            $current_user_id = get_current_user_id();
            $user = $wpdb->get_row("SELECT * FROM {$wpdb->users} WHERE ID = {$current_user_id}");
            $subject = __('About teacher take the test', 'iii-dictionary');
            $message .= __('An teacher account has completed test. Login with administrator account to check the test result.
                                This is infomation of teacher:', 'iii-dictionary') . '<br>';
            $message .= __('Fullname:') . ' ' . $user->display_name . '<br>';
            $message .= __('Username:') . ' ' . $user->user_login . '<br>';
            $message .= __('Email:') . ' ' . $user->user_email . '<br>';
            $message .= '<a href="' . home_url() . '/wp-admin" >' . __('Click here') . '</a> ' . __('to login', 'iii-dictionary');
            $admins = get_users(array('role' => 'mw_super_admin'));
            foreach ($admins as $key => $admin) {
                if ($admin->user_email != '') {
                    wp_mail($admin->user_email, $subject, $message);
                }
            }
        }
        $result = $wpdb->update(
                $wpdb->prefix . 'dict_homework_results', array(
            'finished' => 1,
            'submitted_on' => date('Y-m-d', time()),
            'message' => $feedback
                ), array('id' => $rid)
        );
        $current_user_id = get_current_user_id();
//                        $resultpoint=$wpdb->get_row( "SELECT score FROM wp_dict_homework_results 
//                        WHERE homework_id =  {$_POST['homework_id']} AND userid = {$current_user_id}")->score;
        $resultpoint = $wpdb->get_row("SELECT hdr.score as score,ds.assignment_id as assignment_id FROM wp_dict_homework_results as hdr
                            JOIN wp_dict_homeworks AS dh ON hdr.homework_id = dh.id
                            JOIN wp_dict_sheets AS ds ON ds.id = dh.sheet_id
                            WHERE homework_id =  {$_POST['homework_id']} AND userid = {$current_user_id}");
        if ($result) {
            echo json_encode(array($rid));
        } else {
            echo json_encode(array(0));
        }
        $teacher_test_score_threshold = mw_get_option('teacher-test-score-threshold');
        ik_enqueue_messages('Homework Submitted.', 'success');
        if ($resultpoint->assignment_id != 4) {
            if ($resultpoint->score < $teacher_test_score_threshold) {
                ik_enqueue_messages('Sorry, you correctly answered ' . $resultpoint->score . '%', 'failed');
            } else {
                ik_enqueue_messages('You correctly answer ' . $resultpoint->score . '%', 'success');
            }
        }
        exit;
    }
}

/*
 * update homework score
 */
if ($task == 'grade_homework') {
    $score = $_POST['score'];
    $hrid = $_POST['hrid'];

    if ($score >= 0 && $score <= 100) {
        $result = $wpdb->update(
                $wpdb->prefix . 'dict_homework_results', array('score' => $score, 'correct_answers_count' => $score, 'graded' => 1), array('id' => $hrid)
        );
    }
}

/*
 * Check to see if words exist in given dictionary
 */
if ($task == 'checkword') {
    if (!isset($_GET['dict']) || !is_numeric($_GET['dict'])) {
        die;
    }

    include IK_PLUGIN_DIR . '/library/formatter.php';

    $w = stripslashes($_GET['w']);
    $dict_table = get_dictionary_table($_GET['dict']);

    $words = json_decode($w);
    $words = array_merge($words[0], $words[1]);
    $words_sound = $words;
    $output[0] = $output[1] = $insql = array();

    foreach ($words as $key => $v) {
        if ($v != '') {
            $insql[$key] = "'" . esc_sql($v) . "'";
        } else {
            unset($words[$key]);
            unset($words_sound[$key]);
        }
    }

    $results = $wpdb->get_results('SELECT id, entry, sound, sound_url FROM ' . $wpdb->prefix . $dict_table . ' WHERE entry IN (' . implode(',', $insql) . ')');

    foreach ($results as $item) {
        if (($key = array_search($item->entry, $words)) !== false) {
            unset($words[$key]);
        }

        if (is_null($item->sound_url)) {
            $sound_url = WFormatter::_sound($item->sound, $_GET['dict'], true);
            if ($sound_url != '') {
                $result = $wpdb->update(
                        $wpdb->prefix . $dict_table, array(
                    'sound_url' => $sound_url
                        ), array('id' => $item->id)
                );

                if (($key = array_search($item->entry, $words_sound)) !== false) {
                    unset($words_sound[$key]);
                }
            }
        } else {
            if (($key = array_search($item->entry, $words_sound)) !== false) {
                unset($words_sound[$key]);
            }
        }
    }

    $output[0] = $words;
    $output[1] = $words_sound;

    echo json_encode($output);
    die;
}

/*
 * toggle Sheet state
 */
if ($task == 'shtstate') {
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        die;
    }

    $result = $wpdb->query($wpdb->prepare(
                    'UPDATE ' . $wpdb->prefix . 'dict_sheets SET active = ABS(active - 1) WHERE id = %d', $_POST['id']
    ));
    echo $result ? '1' : '0';
    die;
}

/*
 * Group Homework
 */
if ($task == 'group') {
    if (!isset($route[2])) {
        die;
    }

    $do = $route[2];

    if ($do == 'create') {
        $gname = esc_html($_POST['gname']);
        $gpass = esc_html($_POST['gpasswrd']);
        if (trim($gname) != '' && trim($gpass) != '') {
            if (strpos($gname, ' ') !== false) {
                echo json_encode(array('status' => 0, 'msg' => 'Group name cannot contain spacing!'));
                die;
            }
            $result = $wpdb->query($wpdb->prepare(
                            'SELECT * FROM ' . $wpdb->prefix . 'dict_groups WHERE name = %s', array($gname)
            ));

            if (!$result) {
                $res = $wpdb->insert(
                        $wpdb->prefix . 'dict_groups', array(
                    'name' => $gname,
                    'password' => $gpass,
                    'created_by' => get_current_user_id(),
                    'created_on' => date('Y-m-d', time()),
                    'active' => 1
                        )
                );

                if ($res) {
                    echo json_encode(array('status' => 1, 'msg' => 'Successfully create Group: <em>' . $gname . '</em>', 'id' => $wpdb->insert_id));
                    die;
                } else {
                    echo json_encode(array('status' => 0, 'msg' => 'Can not create Group!'));
                    die;
                }
            } else {
                echo json_encode(array('status' => 0, 'msg' => 'The name, <em>' . $gname . '</em>, is already used. Please try it again with a different name.'));
                die;
            }
        } else {
            echo json_encode(array('status' => 0, 'msg' => 'Group name and Passwords must not be empty!'));
            die;
        }
    }

    if ($do == 'list') {
        $groups = $wpdb->get_results('SELECT id, name FROM ' . $wpdb->prefix . 'dict_groups WHERE created_by = ' . get_current_user_id());
        echo json_encode($groups);
        die;
    }

    if ($do == 'changepass') {
        $apw = stripslashes($_POST['apw']);
        $npw = stripslashes($_POST['npw']);
        $gid = stripslashes($_POST['gid']);

        $user = get_userdata(get_current_user_id());
        if (wp_check_password($apw, $user->user_pass, $user->ID)) {
            $wpdb->update($wpdb->prefix . 'dict_groups', array('password' => $npw), array('id' => $gid));
            echo json_encode(array(1));
        } else {
            echo json_encode(array(0));
        }
    }

    if ($do == 'availability') {
        $gname = $_REAL_POST['gn'];

        $result = $wpdb->get_row($wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'dict_groups WHERE name = %s', $gname));

        if (empty($result)) {
            die('0');
        } else {
            die('1');
        }
    }

    if ($do == 'students') {
        $gid = $_GET['gid'];

        $students = MWDB::get_group_students($gid);
        $output = array();
        foreach ($students as $student) {
            $output[] = array(
                'name' => $student->display_name,
                'email' => $student->user_email,
                'joined_date' => $student->joined_date,
                'done_hw' => $student->homeworks_done
            );
        }

        echo json_encode($output);
        die;
    }

    if ($do == 'assignment') {
        $gid = $_GET['gid'];

        $is_admin = is_mw_super_admin() || is_mw_admin() ? true : false;
        $current_user_id = get_current_user_id();

        $is_math_panel = is_math_panel();

        $current_page = max(1, get_query_var('page'));
        $filter = get_page_filter_session();
        if (empty($filter) && !isset($_POST['filter'])) {
            $filter['orderby'] = 'g.name';
            $filter['order-dir'] = 'asc';
            $filter['items_per_page'] = 25;
            $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
            $filter['created_by'] = get_current_user_id();
            $filter['subscription_status'] = true;
            if ($is_admin) {
                $filter['fetch_classes'] = true;
            }
        } else {
            $filter['created_by'] = $current_user_id;
            if (isset($_POST['filter']['search'])) {
                $filter['group-name'] = $_REAL_POST['filter']['group-name'];
                $filter['class_type'] = $_POST['filter']['class-types'];
            }

            if (isset($_REAL_POST['filter']['orderby'])) {
                $filter['orderby'] = $_REAL_POST['filter']['orderby'];
                $filter['order-dir'] = $_REAL_POST['filter']['order-dir'];
            }

            $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
        }
        $filter['group_id'] = $gid;
        $filter['check_result'] = true;
        $filter['created_by'] = '';
        $filter['offset'] = 0;
        $filter['items_per_page'] = 99999999;
        $assignments = MWDB::get_homework_assignments($filter, $filter['offset'], $filter['items_per_page']);
        $total_pages = ceil($assignments->total / $filter['items_per_page']);

        if (isset($_POST['remove-assignment'])) {
            if (MWDB::remove_homework($_POST['cid']) !== false) {
                wp_redirect(locale_home_url() . '/?r=teachers-box&gid=' . $gid);
                exit;
            }
        }

        set_page_filter_session($filter);

        $output = array();

        foreach ($assignments->items as $assignment) {
            $name = !empty($assignment->name) ? $assignment->name . '<br>' : '';
            $name += sprintf(__('Worksheet: %s', 'iii-dictionary'), $assignment->sheet_name);
            $deadline = $assignment->deadline == '0000-00-00' ? 'None' : ik_date_format($assignment->deadline);

            $output[] = array(
                'name' => $name,
                'grade' => $assignment->grade,
                'assigned_date' => ik_date_format($assignment->created_on),
                'deadline' => $deadline,
                'ordering' => $assignment->ordering,
                'sheet_id' => $assignment->sheet_id,
                'id' => $assignment->id,
                'active' => $assignment->active
            );
        }

        echo json_encode($output);
        die;
    }

    if ($do == 'changeactive') {
        $assignmentid = $_GET['assignmentid'];

        $data['id'] = $assignmentid;
        $data['active'] = 1;
        MWDB::update_homework_assignment($data);

        die;
    }

    if ($do == 'changdeactive') {
        $assignmentid = $_GET['assignmentid'];

        $data['id'] = $assignmentid;
        $data['active'] = 0;
        MWDB::update_homework_assignment($data);

        die;
    }
}

/*
 * User availability
 */
if ($task == 'availability') {
    if ($do == 'user') {
        $user_login = $_GET['user_login'];

        $user = $wpdb->get_row($wpdb->prepare('SELECT user_login FROM ' . $wpdb->users . ' WHERE user_login = %s', $user_login));

        if ($user) {
            echo json_encode(array(0));
            die;
        }

        $user = $wpdb->get_row($wpdb->prepare('SELECT user_login FROM ' . $wpdb->users . ' WHERE user_email = %s', $user_login));

        if ($user) {
            echo json_encode(array(0));
            die;
        }

        echo json_encode(array(1));
        die;
    }
}

/*
 * User info
 */
if ($task == 'user') {
    if ($do == 'passcheck') {
        $user = get_userdata(get_current_user_id());
        if (wp_check_password($_POST['pw'], $user->user_pass, $user->ID)) {
            echo json_encode(array(1));
        } else {
            echo json_encode(array(0));
        }
        exit;
    }
}

/*
 * validate creadit code
 */
if ($task == 'validatecredit') {
    $credit_code = $_POST['c'];

    $code = $wpdb->get_row(
            $wpdb->prepare('SELECT c.*, us.activated_by, COUNT(activated_by) AS activated_times
							FROM ' . $wpdb->prefix . 'dict_credit_codes AS c
							LEFT JOIN ' . $wpdb->prefix . 'dict_user_subscription AS us ON us.activation_code_id = c.id
							WHERE encoded_code = %s', $_POST['c'])
    );

    if (is_null($code)) {
        $json['status'] = 0;
        $json['title'] = __('Invalid credit code number.', 'iii-dictionary');
        $json['msg'] = __('The credit code you entered is invalid. Please enter a different one.', 'iii-dictionary');
    } else if ($code->activated_by && ($code->typeid == 1 || $code->typeid == 3 || $code->typeid == 4)) {
        $json['status'] = 0;
        $json['title'] = __('This credit code has been used already.', 'iii-dictionary');
        $json['msg'] = __('Please enter a different credit code.', 'iii-dictionary');
    } else if (!$code->active) {
        $json['status'] = 0;
        $json['title'] = __('This credit code has expired.', 'iii-dictionary');
        $json['msg'] = __('This credit code has already expired. Please enter a different one.', 'iii-dictionary');
    } else if ($code->activated_times == $code->no_of_students && $code->typeid == 2) {
        $json['status'] = 0;
        if (is_numeric($code->activated_by)) {
            $json['title'] = __('Activation error', 'iii-dictionary');
            $json['msg'] = __('Number of license is used up for this activation code. Please enter a different code.', 'iii-dictionary');
        } else {
            $json['title'] = __('Activation notice', 'iii-dictionary');
            $json['msg'] = __('This activation code is already actived from Desktop app. Please use the Desktop icon to start iklearn.com.', 'iii-dictionary');
        }
    } else {
        $json['status'] = 1;
        $json['ltype'] = (int) $code->typeid;
        $json['did'] = (int) $code->dictionary_id;
        $json['size'] = $code->no_of_students;
    }

    echo json_encode($json);
    die;
}

/*
 * flash cards
 */
if ($task == 'flashcard') {
    $dictionary_id = get_dictionary_id_by_slug($_REAL_POST['did']);

    $is_dictionary_subscribed = is_dictionary_subscribed($dictionary_id);

    if ($do == 'addfolder') {
        if (!$is_dictionary_subscribed) {
            die(json_encode(array(0)));
        }

        $name = $_REAL_POST['n'];

        $result = $wpdb->insert(
                $wpdb->prefix . 'dict_flashcard_folders', array(
            'user_id' => get_current_user_id(),
            'dictionary_id' => $dictionary_id,
            'name' => $name
                )
        );

        if ($result) {
            die(json_encode(array($wpdb->insert_id)));
        } else {
            die(json_encode(array(0)));
        }
    }

    if ($do == 'addcard') {
        $current_user_id = get_current_user_id();

        if (!$is_dictionary_subscribed) {
            $cards = $wpdb->get_col('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'dict_flashcards WHERE created_by = ' . $current_user_id . ' AND dictionary_id = ' . $dictionary_id);

            // free user can add up to 5 flash cards
            if ($cards[0] >= 5) {
                echo json_encode(array('status' => 2));
                die;
            }
        }

        $entry = $_REAL_POST['e'];
        $folder_id = $_REAL_POST['fid'];

        $result = $wpdb->insert(
                $wpdb->prefix . 'dict_flashcards', array(
            'created_by' => $current_user_id,
            'folder_id' => $folder_id,
            'group_id' => 0,
            'dictionary_id' => $dictionary_id,
            'word' => $entry
                )
        );

        $result2 = $wpdb->insert(
                $wpdb->prefix . 'dict_flashcard_userdata', array(
            'flashcard_id' => $wpdb->insert_id,
            'user_id' => $current_user_id
                )
        );

        if ($result) {
            echo json_encode(array('status' => 1));
        } else {
            echo json_encode(array('status' => 0));
        }

        die;
    }

    if ($do == 'savenotes') {
        $current_user_id = get_current_user_id();
        $existing = $wpdb->get_row('SELECT id FROM ' . $wpdb->prefix . 'dict_flashcard_userdata WHERE flashcard_id = ' . esc_sql($_POST['id']) . ' AND user_id = ' . $current_user_id);

        if (empty($existing)) {
            $result = $wpdb->insert(
                    $wpdb->prefix . 'dict_flashcard_userdata', array(
                'flashcard_id' => $_POST['id'],
                'user_id' => $current_user_id,
                'notes' => $_REAL_POST['notes']
                    )
            );
        } else {
            $result = $wpdb->update(
                    $wpdb->prefix . 'dict_flashcard_userdata', array(
                'notes' => $_REAL_POST['notes']
                    ), array(
                'flashcard_id' => $_POST['id'],
                'user_id' => $current_user_id
                    )
            );
        }

        if ($result !== false) {
            die(json_encode(array(1)));
        } else {
            die(json_encode(array(0)));
        }
    }

    if ($do == 'memorized') {
        $current_user_id = get_current_user_id();
        $flashcard_id = esc_sql($_POST['id']);
        $existing = $wpdb->get_row('SELECT id FROM ' . $wpdb->prefix . 'dict_flashcard_userdata WHERE flashcard_id = ' . $flashcard_id . ' AND user_id = ' . $current_user_id);

        if (isset($_POST['memorized'])) {
            $value = 1;
        } else {
            $value = 'ABS(memorized - 1)';
        }

        if (empty($existing)) {
            $wpdb->insert(
                    $wpdb->prefix . 'dict_flashcard_userdata', array(
                'flashcard_id' => $flashcard_id,
                'user_id' => $current_user_id,
                'memorized' => 1
                    )
            );
        } else {
            $wpdb->query('UPDATE ' . $wpdb->prefix . 'dict_flashcard_userdata 
							  SET memorized = ' . $value . '
							  WHERE flashcard_id = ' . $flashcard_id . ' AND user_id = ' . $current_user_id);
        }

        die;
    }

    if ($do == 'delete') {
        $current_user_id = get_current_user_id();

        $result = $wpdb->delete(
                $wpdb->prefix . 'dict_flashcards', array(
            'id' => $_POST['id'],
            'created_by' => $current_user_id
                )
        );

        $wpdb->delete(
                $wpdb->prefix . 'dict_flashcard_userdata', array(
            'flashcard_id' => $_POST['id'],
            'user_id' => $current_user_id
                )
        );

        if ($result !== false) {
            die(json_encode(array(1)));
        } else {
            die(json_encode(array(0)));
        }
    }

    if ($do == 'lookup') {
        include IK_PLUGIN_DIR . '/library/formatter.php';

        $flashcard = $wpdb->get_row($wpdb->prepare('SELECT word, dictionary_id FROM ' . $wpdb->prefix . 'dict_flashcards WHERE id = %d', $_GET['id']));

        $dictionary_table = get_dictionary_table($flashcard->dictionary_id);

        $word = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . $dictionary_table . ' WHERE entry = \'' . $flashcard->word . '\'');

        $html = '<div id="headword">' . WFormatter::_hw($word->headword) . '</div>' .
                '<div id="pronunciation">' .
                WFormatter::_sound($word->sound, $flashcard->dictionary_id) .
                WFormatter::_pr($word->pronunciation) .
                '<span class="functional-label">' . WFormatter::_fl($word->functional_label) . '</span>' .
                '</div>' .
                '<div id="definition">' .
                WFormatter::_def($word->definition, $flashcard->dictionary_id) .
                '</div>';

        echo $html;

        die;
    }
}

// grade api
if ($task === 'grade') {
    if ($do === 'add') {
        $data['parent_id'] = $_POST['parent_id'];
        $data['name'] = $_POST['name'];
        $data['type'] = $_POST['type'];
        $data['level'] = $_POST['level'];

        if ($last_id = MWDB::store_grade($data)) {
            echo $last_id;
        } else {
            echo '0';
        }
        exit;
    }

    if ($do == 'rename') {
        $data['id'] = $_POST['id'];
        $data['name'] = $_REAL_POST['n'];

        if ($last_id = MWDB::store_grade($data)) {
            echo $last_id;
        } else {
            echo '0';
        }
        exit;
    }

    if ($do == 'changelastpage') {
        $data['id'] = $_POST['id'];
        $data['lastpage'] = $_REAL_POST['check'];

        if (MWDB::store_sheet_page($data)) {
            echo 'update success';
        } else {
            echo 'update error';
        }
        exit;
    }

    if ($do == 'change_order') {
        $dir = $_POST['dir'];

        if ($dir == 'up') {
            MWDB::set_grade_order_up($_POST['id']);
        } else if ($dir == 'down') {
            MWDB::set_grade_order_down($_POST['id']);
        }
    }
}

if ($task === 'math_worksheet') {
    if ($do === 'get') {
        //check user subscription
        $flag = '';
        if (isset($_GET['lid'])) {
            //if(!is_homework_tools_subscribed() || !is_mw_super_admin() || !is_mw_admin() || !(!is_user_logged_in() && isset($_GET['ncl']) && $_GET['ncl'] < 2)) {
            if (!is_math_homework_tools_subscribed() || !is_user_logged_in()) {
                $flag = 'text-muted';
            }
            if (is_mw_super_admin() || is_mw_admin()) {
                $flag = '';
            }
        }
        $query = 'SELECT ms.id, sheet_name , homework_type_id
						FROM ' . $wpdb->prefix . 'dict_sheets AS ms
						JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = ms.grade_id
						JOIN (
							SELECT id, name AS level_name, parent_id AS level_parent_id 
							FROM ' . $wpdb->prefix . 'dict_grades WHERE level = 1
						) AS lgr ON lgr.id = gr.parent_id
						JOIN (
							SELECT id, name AS level_category_name 
							FROM ' . $wpdb->prefix . 'dict_grades WHERE level = 0
						) AS cgr ON cgr.id = lgr.level_parent_id';

        if (!empty($_GET['cid'])) {
            $cat_id = $_GET['cid'];
            $where[] = 'cgr.id = %d';
            $params[] = $cat_id;
        }

        if (!empty($_GET['plid'])) {
            $level_id = $_GET['plid'];
            $where[] = 'lgr.id = %d';
            $params[] = $level_id;
        }

        if (!empty($_GET['lid'])) {
            $sublevel_id = $_GET['lid'];
            $where[] = 'grade_id = %d';
            $params[] = $sublevel_id;
        }

        if (!empty($_GET['name'])) {
            $sheet_name = $_GET['name'];
            $where[] = 'sheet_name LIKE %s';
            $params[] = '%' . $sheet_name . '%';
        }

        if (!empty($_GET['exclude'])) {
            $where[] = 'ms.id <> %s';
            $params[] = $_GET['exclude'];
        }
        /*
          if(!is_math_homework_tools_subscribed()) {
          $where[] = 'homework_type_id <> ' . HOMEWORK_SUBSCRIBED;
          }
         */

        if (!empty($where)) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }

        $query .= ' ORDER BY ms.ordering';

        $worksheets = $wpdb->get_results(
                $wpdb->prepare($query, $params)
        );
        $is_sub = get_ws_subscribed();
        $json = array();
        foreach ($worksheets as $worksheet) {
            $json[] = array('sid' => $worksheet->id, 'name' => $worksheet->sheet_name, 'sub' => $flag, 'type' => $worksheet->homework_type_id, 'is' => $is_sub);
        }

        echo json_encode($json);
        exit;
    }
}

if ($task === 'worksheet') {
    if ($do === 'get') {
        $query = 'SELECT [columns]
					  FROM ' . $wpdb->prefix . 'dict_sheets AS s
					  JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = s.grade_id';

        $columns[] = 's.*, gr.name AS grade';

        $where[] = 'category_id <> 5';

        if ($_GET['assignment_name']) {
            $columns[] = 'hal.name as aname';
            $query .= ' JOIN ' . $wpdb->prefix . 'dict_homework_assignment_langs AS hal ON hal.assignment_id = s.assignment_id AND hal.lang = \'' . get_short_lang_code() . '\'';
        }

        if (!empty($_GET['name'])) {
            $sheet_name = $_GET['name'];
            $where[] = 'sheet_name LIKE %s';
            $params[] = '%' . $sheet_name . '%';
        }

        if (!empty($_GET['assignment'])) {
            $where[] = 'assignment_id = %d';
            $params[] = $_GET['assignment'];
        }

        if (!empty($_GET['type'])) {
            $where[] = 'homework_type_id = %d';
            $params[] = $_GET['type'];
        }

        if (!empty($_GET['grade'])) {
            $where[] = 'gr.name = %d';
            $params[] = $_GET['grade'];
        }

        if (!empty($_GET['exclude'])) {
            $where[] = 's.id <> %s';
            $params[] = $_GET['exclude'];
        }

        if (!empty($where)) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }

        $query = str_replace('[columns]', implode(',', $columns), $query);

        $worksheets = $wpdb->get_results(
                $wpdb->prepare($query, $params)
        );

        $json = array();
        foreach ($worksheets as $worksheet) {
            $item = array('sid' => $worksheet->id, 'name' => $worksheet->sheet_name);
            if ($_GET['assignment_name']) {
                $item['aname'] = $worksheet->aname;
            }
            if ($_GET['grade_name']) {
                $item['grade'] = $worksheet->grade;
            }
            $json[] = $item;
        }

        echo json_encode($json);
        exit;
    }
}
if ($task === 'worksheetmath') {
    if ($do === 'get') {
        $query = 'SELECT [columns]
					  FROM ' . $wpdb->prefix . 'dict_sheets AS s
					  JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = s.grade_id';

        $columns[] = 's.*, gr.name AS grade';

        $where[] = 'category_id = 5';

        if ($_GET['assignment_name']) {
            $columns[] = 'hal.name as aname';
            $query .= ' JOIN ' . $wpdb->prefix . 'dict_homework_assignment_langs AS hal ON hal.assignment_id = s.assignment_id AND hal.lang = \'' . get_short_lang_code() . '\'';
        }

        if (!empty($_GET['name'])) {
            $sheet_name = $_GET['name'];
            $where[] = 'sheet_name LIKE %s';
            $params[] = '%' . $sheet_name . '%';
        }

        if (!empty($_GET['assignment'])) {
            $where[] = 'assignment_id = %d';
            $params[] = $_GET['assignment'];
        }

        if (!empty($_GET['type'])) {
            $where[] = 'homework_type_id = %d';
            $params[] = $_GET['type'];
        }

        if (!empty($_GET['grade'])) {
            $where[] = 'gr.name = %d';
            $params[] = $_GET['grade'];
        }

        if (!empty($_GET['exclude'])) {
            $where[] = 's.id <> %s';
            $params[] = $_GET['exclude'];
        }

        if (!empty($where)) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }

        $query = str_replace('[columns]', implode(',', $columns), $query);

        $worksheets = $wpdb->get_results(
                $wpdb->prepare($query, $params)
        );

        $json = array();
        foreach ($worksheets as $worksheet) {
            $item = array('sid' => $worksheet->id, 'name' => $worksheet->sheet_name);
            if ($_GET['assignment_name']) {
                $item['aname'] = $worksheet->aname;
            }
            if ($_GET['grade_name']) {
                $item['grade'] = $worksheet->grade;
            }
            $json[] = $item;
        }

        echo json_encode($json);
        exit;
    }
}
if ($task == 'mw_download') {
    $is_login = $_GET['is_login'];
    if ($is_login == 0) {
        $json['status'] = 0;
    } else {
        $json['status'] = 1;
    }
    echo json_encode($json);
    exit;
}

if ($task == 'status_msg') {
    global $wpdb;
    $id = $_POST['id'];
    if ($id != 0) {
        $result = $wpdb->query(
                'UPDATE ' . $wpdb->prefix . 'dict_private_message_inbox 
				SET status = 1 WHERE id = ' . $id
        );
    }
    exit;
}

if ($task == 'get_sub_dic') {
    $sub_folder = $_POST['sub'];
    $html = '<option value="" >' . __('Select a directory', 'iii-dictionary') . '</option>';
    if (!empty($sub_folder)) {
        foreach (glob($sub_folder . '/*', GLOB_ONLYDIR) as $data) {
            $selected = ($_SESSION['media']['sub-dic'] == basename($data)) ? 'selected' : '';
            $html .= '<option value="' . basename($data) . '"' . $selected . '>' . basename($data) . '</option>';
        }
    }

    echo $html;
    exit;
}

if ($task == 'chat') {
    if ($do === 'request') {
        global $wpdb;
        $_sheet_id = $_POST['sid'];
        $_user_id = $_POST['id'];

        $_points = ik_get_user_points($_user_id);
        $_price_chat = mw_get_option('math-chat-price');
        $_return = (isset($_POST['return'])) ? trim($_POST['return']) : '';

        if ($_points < $_price_chat) {
            $html = '<div class="col-md-12 block-respone-content">' . __('Sorry, you do not have enough points for this session', 'iii-dictionary') . '</div>';
            $html .= '<div class="col-md-12 block-respone-question">' . __('Would you like to purchase points now?', 'iii-dictionary') . '</div>';
            $html .= '<div class="col-md-12 block-popup-btn"><div class="row">';
            $html .= '<div class="col-md-6"><button name="btn-purchase-points" type="submit" form="main-form" class="btn-popup-style">' . __('Yes', 'iii-dictionary') . '</button></div>';
            $html .= '<div class="col-md-6"><button class="btn-popup-style btn-close-bp ' . $_check . '">' . __('No', 'iii-dictionary') . '</button></div>';
            $html .= '<input form="main-form" type="hidden" name="return-math" value="' . $_return . '" />';
            $html .= '</div></div>';
        } else {
            $data = array(
                'sheet_id' => $_sheet_id,
                'user_id' => $_user_id,
                'teacher_id' => 0,
                'price' => $_price_chat,
                'datetime' => date('Y-m-d', time()),
                'url' => $_return,
                'status' => 0
            );
            $check_exists = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'dict_chat_session AS dcs 
												WHERE dcs.sheet_id = ' . esc_sql($_sheet_id) . ' AND dcs.user_id = ' . esc_sql($_user_id) . ' AND dcs.status != 2');

            if (count($check_exists) == 0) {
                $result = $wpdb->insert($wpdb->prefix . 'dict_chat_session', $data);
            }

            switch ($check_exists[0]->status) {
                case 1 :
                    $teacher = get_userdata($check_exists[0]->teacher_id);
                    $html .= '<div id="block-start">';
                    $html .= '<div class="col-md-12">' . __('A teacher, ', 'iii-dictionary') . '' . $teacher->user_email . '' . __(' has responded.', 'iii-dictionary');
                    $html .= '<div class="col-md-12">' . __('Would you like to start the tutoring now ?', 'iii-dictionary') . '</div>';
                    $html .= '<div class="col-md-6 col-md-offset-6"><button data-teacher="' . $teacher->user_email . '" id="start-session" class="btn-popup-style">' . __('Start', 'iii-dictionary') . '</button></div>';
                    $html .= '</div>';
                    break;
                case 2 :
                    break;
                default :
                    $html = '<div class="col-md-12 block-respone-content">' . __('Your request has been sent to the teacher\'s panel.', 'iii-dictionary') . '</div>';
                    $html .= '<div class="col-md-12 block-respone-wait">' . __('Please wait until a teacher responses', 'iii-dictionary') . '</div>';
                    $html .= '<div class="col-md-6 col-md-offset-6"><button class="btn-popup-style btn-cancel-session">' . __('No', 'iii-dictionary') . '</button></div>';
                    break;
            }
            //store request chat to database
        }
        echo $html;
        exit;
    }

    if ($do === 'notice') {
        $id = $_POST['id'];
        switch ($id) {
            case 7 :
                $_id = ( $_POST['_id'] ) ? $_POST['_id'] : '';
                $wpdb->update($wpdb->prefix . 'dict_chat_session', array('room' => 2), array('id' => $_id));
                break;
            case 2 :
                $html = '<div class="col-md-12 block-respone-content">' . __('Sorry, you do not have enough points for this session', 'iii-dictionary') . '</div>';
                $html .= '<div class="col-md-12 block-respone-question">' . __('Would you like to purchase points now?', 'iii-dictionary') . '</div>';
                $html .= '<div class="col-md-12 block-popup-btn"><div class="row">';
                $html .= '<div class="col-md-6"><button name="btn-purchase-points" type="submit" form="main-form" class="btn-popup-style">' . __('Yes', 'iii-dictionary') . '</button></div>';
                $html .= '<div class="col-md-6"><button class="btn-popup-style btn-quit">' . __('No', 'iii-dictionary') . '</button></div>';
                $html .= '<input form="main-form" type="hidden" name="return-math" value="' . $_POST['return'] . '" />';
                $html .= '</div></div>';
                break;
            case 1 :
                $html = '<p style="padding-left: 20%; ">Do you want to quit tutoring?</p>';
                break;
            case 0 :
                $is_con = ( $_POST['is_con'] ) ? 'not_enough' : 'continue_session';
                $html = '<div class="col-md-12 block-continue-content">' . __('Do you want continue this session ?', 'iii-dictionary') . '</div>';
                $html .= '<div class="col-md-6"><button class="btn-popup-style ' . $is_con . '">' . __('Yes', 'iii-dictionary') . '</button></div>';
                $html .= '<div class="col-md-6"><button class="btn-popup-style btn-quit">' . __('No', 'iii-dictionary') . '</button></div>';
                break;
        }

        echo $html;
    }
    if ($do === 'update_quit_status') {
        global $wpdb;
        $_id = ( $_POST['_id'] ) ? $_POST['_id'] : '';
        $id = ( $_POST['id'] ) ? $_POST['id'] : '';
        $wpdb->update($wpdb->prefix . 'dict_chat_session', array('quit_status' => $id), array('id' => $_id));
    }
    if ($do === 'update_session') {
        global $wpdb;
        $data = array(
            'teacher_id' => $_POST['teacher_id'],
            'status' => 1
        );
        $result = $wpdb->update($wpdb->prefix . 'dict_chat_session', $data, array('id' => $_POST['id']));
        echo $result;
        exit;
    }

    if ($do === 'insert_history') {
        global $wpdb;
        $data = array(
            'from_id' => $_POST['from_id'],
            'to_id' => $_POST['to_id'],
            'from_time' => $_POST['from_time'],
            'content' => $_POST['content'],
            'room' => $_POST['room'],
        );
        $chat_session = $wpdb->get_row('SELECT room FROM ' . $wpdb->prefix . 'dict_chat_session WHERE id = ' . esc_sql($_POST['idroom']));
        if ($chat_session->room == 0) {
            $wpdb->insert($wpdb->prefix . 'dict_chat_history', $data);
            echo $wpdb->insert_id;
            exit;
        } else {
            echo 1;
            exit;
        }
    }

    if ($do === 'update_history') {
        global $wpdb;
        $id = (filter_var($_POST['id'], FILTER_VALIDATE_INT)) ? $_POST['id'] : 0;
        $wpdb->update($wpdb->prefix . 'dict_chat_history', array('to_time' => $_POST['to_time']), array('id' => $id));

        echo $id;
        exit;
    }

    if ($do === 'get_history') {
        global $wpdb;
        $html = '';
        $id = (filter_var($_POST['id'], FILTER_VALIDATE_INT)) ? $_POST['id'] : 0;
        $room = $_POST['room'];
        $student = $_POST['idstudent'];
        $teacher = $_POST['idteacher'];

        $results = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'dict_chat_history AS dch WHERE dch.room = ' . esc_sql($room) . ' ORDER  BY id DESC');
//                        if($results[0]->from_id == $student){
//                                $html .= '<span class="wplc-user-message" style="text-decoration: none;"> Student : '.  get_user_by('id', $results[0]->from_id)->user_nicename .'</span><div class="wplc-clear-float-message"></div>';
//                                $html .= '<span class="wplc-admin-message " style="text-decoration: none;"> Tutor : '.  get_user_by('id', $results[0]->to_id)->user_nicename .'</span><br /><div class="wplc-clear-float-message"></div>';
//                        }else{
//                                $html .= '<span class="wplc-user-message " style="text-decoration: none;"> Tutor : '.  get_user_by('id', $results[0]->to_id)->user_nicename .'</span><div class="wplc-clear-float-message"></div>';
//                                $html .= '<span class="wplc-admin-message "  style="text-decoration: none;"> Student : '.  get_user_by('id', $results[0]->from_id)->user_nicename .'</span><br /><div class="wplc-clear-float-message"></div>';
//                            
//                        }
        foreach ($results AS $data) {
            if ($data->from_id == $id) {
                $html .= '<span class="wplc-user-message ">Tutor : ' . wp_unslash($data->content) . '</span><div class="wplc-clear-float-message"></div>';
            } else {
                $html .= '<span class="wplc-admin-message  "><strong></strong>Student : ' . wp_unslash($data->content) . '</span><br /><div class="wplc-clear-float-message"></div>';
            }
        }
        echo $html;
        exit;
    }

    if ($do === 'start_session') {
        global $wpdb;
        $sid = (filter_var($_POST['sid'], FILTER_VALIDATE_INT)) ? $_POST['sid'] : 0;
        $uid = (filter_var($_POST['uid'], FILTER_VALIDATE_INT)) ? $_POST['uid'] : 0;

        $result = $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'dict_chat_session WHERE sheet_id = ' . $sid . ' AND user_id= ' . $uid);

        echo ($result > 0) ? true : false;
        exit;
    }

    if ($do === 'update_points') {
        global $wpdb;
        $uid = (filter_var($_POST['uid'], FILTER_VALIDATE_INT)) ? $_POST['uid'] : 0;
        $tid = (filter_var($_POST['tid'], FILTER_VALIDATE_INT)) ? $_POST['tid'] : 0;
        $points = ik_get_user_points($uid);
        $t_points = ik_get_user_points($tid);
        $price = mw_get_option('math-chat-price');
        if ($points >= $price) {
            $update_points = $points - $price;
            $t_update_points = $_points + $price;
            update_user_meta($uid, 'user_points', $update_points);
            update_user_meta($tid, 'user_points', $t_update_points);
            echo $update_points;
            exit;
        }
        echo 0;
        exit;
    }

    if ($do === 'clear_session') {
        global $wpdb;
        $sid = (filter_var($_POST['sid'], FILTER_VALIDATE_INT)) ? $_POST['sid'] : 0;
        $uid = (filter_var($_POST['uid'], FILTER_VALIDATE_INT)) ? $_POST['uid'] : 0;

        $wpdb->update($wpdb->prefix . 'dict_chat_session', array('status' => 2), array('sheet_id' => $sid, 'user_id' => $uid));

        echo 1;
        exit;
    }

    if ($do === 'cancel_session') {
        global $wpdb;
        $sid = (filter_var($_POST['sid'], FILTER_VALIDATE_INT)) ? $_POST['sid'] : 0;
        $uid = (filter_var($_POST['uid'], FILTER_VALIDATE_INT)) ? $_POST['uid'] : 0;

        $wpdb->delete($wpdb->prefix . 'dict_chat_session', array('sheet_id' => $sid, 'user_id' => $uid));

        echo 1;
        exit;
    }
}
if ($task == "set-ordering") {
    global $wpdb;
    $id = $_REQUEST["id"];
    $number = $_REQUEST["number"];
    $order = MWDB::get_order_sheet($id);
//            echo($order->ordering) ;die; 
    $result = $wpdb->query(
            'UPDATE ' . $wpdb->prefix . 'dict_sheets SET ordering = ' . $number . ' WHERE id=' . $id
    );
    exit;
}

if ($task == "update_edit_class") {
    global $wpdb;
    $price = $_REQUEST["price"];
    $group_id = $_REQUEST["group_id"];
    $about_class = $_REQUEST["about_class"];
    $name = $_REQUEST["name"];
    $check_box_fee = $_REQUEST["check_box_fee"];
    $current_password = $_REQUEST["current_password"];
    $new_password = $_REQUEST["new_password"];

    $html = '';
    $valid = true;
    if (trim($name) == '') {
        $html .= '<div class="error-message">';
        $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>: <b>' . __('Rename the Class', 'iii-dictionary') . '</b>' . __(' can not be empty.', 'iii-dictionary');
        $html .= '</div>';
        $valid = false;
    }

    // check for duplication in group name
    $query = 'SELECT * FROM ' . $wpdb->prefix . 'dict_groups WHERE name = \'' . esc_sql($name) . '\'';
    if (!empty($group_id)) {
        $query .= ' AND id <> ' . esc_sql($group_id);
    }
    $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'dict_groups 
															WHERE id = %d', $group_id));
    $pass = isset($row->password) ? $row->password : "";
    if ($wpdb->query($query) && trim($name) != '') {
        $html .= '<div class="error-message">';
        $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>: <b>' . __('Rename the Class', 'iii-dictionary') . '</b>' . __(' is already used. Please try it again with a different name.', 'iii-dictionary');
        $html .= '</div>';
        $valid = false;
    }

    if ($pass != $current_password) {
        $html .= '<div class="error-message">';
        $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>: <b>' . __('Type Current Password', 'iii-dictionary') . '</b>' . __(' incorrect.', 'iii-dictionary');
        $html .= '</div>';
        $valid = false;
    }
    if ($pass == $current_password && trim($new_password) == '') {
        $html .= '<div class="error-message">';
        $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>: <b>' . __('Type New Password', 'iii-dictionary') . '</b>' . __(' can not be empty.', 'iii-dictionary');
        $html .= '</div>';
        $valid = false;
    }
    if ($check_box_fee == '0' && $price == 0) {
        $html .= '<div class="error-message">';
        $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>: <b>' . __('Change Price', 'iii-dictionary') . '</b>' . __(' can not be empty.', 'iii-dictionary');
        $html .= '</div>';
        $valid = false;
    }
    if ($valid) {
        MWDB::update_edit_class($group_id, $name, $price, $about_class, $new_password);
        echo 'ok';
    } else {
        echo $html;
    }
    exit;
}
if ($task == "get_message_receive") {
    $id = $_REQUEST['gid'];
    $data = MWDB::get_message_receive($id);

    $html = "";
    if (count($data) >= 1):

        foreach ($data as $value):
            $html .= '<tr>';
            $html .= '<td class="subject" data-id="' . $value->id . '" data-subject="' . $value->subject . '"  data-sender="' . $value->display_name . '" data-receivedDate="' . date('Y-m-d / h:i a', strtotime($value->received_on)) . '" data-message="' . $value->message . '">' . wp_trim_words($value->subject, 8, '...') . '</td>';
            $html .= '<td>' . $value->display_name . '</td>';
            $html .= '<td >' . date('Y-m-d / h:i a', strtotime($value->received_on)) . '</td>';
            $html .= '<td class="del-subject" data-id="' . $value->id . '" data-type="receive" style="width: 50px;border-left: 3px solid #ffffff !important; text-align: center; cursor: pointer;"></td>';
            $html .= '</tr>';
        endforeach;
        for ($i = count($data); $i < 11; $i++):
            $html .= '<tr>';
            $html .= '<td>&nbsp</td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '</tr>';
        endfor;
    else:

        $html .= '<tr>';
        $html .= '<td>There is no private message yet.</td>';
        $html .= '<td></td>';
        $html .= '<td></td>';
        $html .= '<td></td>';
        $html .= '</tr>';
        for ($i = 0; $i < 10; $i++):
            $html .= '<tr>';
            $html .= '<td>&nbsp</td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '</tr>';
        endfor;
    endif;


    echo $html;
    //    var_dump($data);
    die;
}
if ($task == "get_message_sent") {
    $id = $_REQUEST['gid'];
    $data = MWDB::get_message_sent($id);

    $html = "";


    if (count($data) >= 1):
        foreach ($data as $value):
            $html .= '<tr>';
            $html .= '<td class="subject-sent" data-id="' . $value->id . '" data-subject="' . $value->subject . '"  data-sender="' . $value->display_name . '" data-sentDate="' . date('Y-m-d / h:i a', strtotime($value->sent_on)) . '" data-message="' . $value->message . '">' . wp_trim_words($value->subject, 8, '...') . '</td>';
            $html .= '<td>' . $value->display_name . '</td>';
            $html .= '<td>' . date('Y-m-d / h:i a', strtotime($value->sent_on)) . '</td>';
            $html .= '<td class="del-subject" data-id="' . $value->id . '" data-type="sent" style="width: 50px;border-left: 3px solid #ffffff !important; text-align: center; cursor: pointer;"></td>';
            $html .= '</tr>';
        endforeach;
        for ($i = count($data); $i < 11; $i++):
            $html .= '<tr>';
            $html .= '<td>&nbsp</td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '</tr>';
        endfor;
    else:
        $html .= '<tr>';
        $html .= '<td>There is no sent message.</td>';
        $html .= '<td></td>';
        $html .= '<td></td>';
        $html .= '<td></td>';
        $html .= '</tr>';
        for ($i = 0; $i < 10; $i++):
            $html .= '<tr>';
            $html .= '<td>&nbsp</td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '</tr>';
        endfor;
    endif;

    echo $html;
    //var_dump($data);
    die;
}
if ($task == "delete_message") {
    $id = $_REQUEST['id'];
    $type = $_REQUEST['type'];
    if ($type == 'sent') {
        $message = MWDB::get_sent_private_message($id);
        if ($message) {
            $wpdb->delete(
                    $wpdb->prefix . 'dict_messages', array(
                'id' => $message->message_id
                    )
            );

            $wpdb->delete(
                    $wpdb->prefix . 'dict_private_message_outbox', array(
                'id' => $id
                    )
            );
        }
    } else {
        $message = MWDB::get_received_private_message($id);
        if ($message) {
            $wpdb->delete(
                    $wpdb->prefix . 'dict_messages', array(
                'id' => $message->message_id
                    )
            );

            $wpdb->delete(
                    $wpdb->prefix . 'dict_private_message_inbox', array(
                'id' => $id
                    )
            );
        }
    }
    echo 'ok';
    die;
}
if ($task == "save_message") {
    $id = $_REQUEST['id'];
    $recipient_id = $_REQUEST['recipient_id'];
    $subject = $_REQUEST['subject'];
    $message = $_REQUEST['message'];

    $user_id = get_current_user_id();
    $created_on = date('Y-m-d H:i:s', time());
    $display_at_login = 0;
    $system_message = 0;

    $message_id = MWDB::insert_private_message(array(
                'subject' => $subject,
                'message' => $message,
                'created_on' => $created_on
    ));

    if ($message_id) {
        $data = array(
            'user_id' => $recipient_id,
            'message_id' => $message_id,
            'sender_id' => $user_id,
            'group_id' => $id,
            'received_on' => $created_on,
            'status' => MESSAGE_STATUS_UNREAD,
            'moderation_status' => MESSAGE_MOD_STATUS_ACTIVE,
            'system_message' => $system_message,
            'display_at_login' => $display_at_login
        );

        MWDB::insert_private_message_inbox($data);

        $data_outbox = array(
            'group_id' => $id,
            'user_id' => $user_id,
            'message_id' => $message_id,
            'recipient_id' => $recipient_id,
            'sent_on' => $created_on,
            'system_message' => $system_message,
            'display_at_login' => $display_at_login
        );

        MWDB::insert_private_message_outbox($data_outbox);

        echo 1;
    } else {
        echo 0;
    }
    die;
}
if ($task == "get_class_postings") {
    $id = $_REQUEST['gid'];
    $data = MWDB::get_group_messages($id);

    $html = "";


    if (count($data) >= 1):
        foreach ($data as $value):
            $html .= '<tr>';
            $html .= '<td class="subject-posting" data-id="' . $value->id . '" data-subject="' . $value->subject . '"  data-student="' . $value->student_name . '" data-date="' . date('Y-m-d / h:i a', strtotime($value->posted_on)) . '" data-message="' . $value->message . '">' . wp_trim_words($value->subject, 8, '...') . '</td>';
            $html .= '<td>' . $value->student_name . '</td>';
            $html .= '<td>' . date('Y-m-d / h:i a', strtotime($value->posted_on)) . '</td>';
            $html .= '<td class="del-subject-posting" data-id="' . $value->id . '" style="width: 50px;border-left: 3px solid #ffffff !important; text-align: center; cursor: pointer;"></td>';
            $html .= '</tr>';
        endforeach;
        for ($i = count($data); $i < 11; $i++):
            $html .= '<tr>';
            $html .= '<td>&nbsp</td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '</tr>';
        endfor;
    else:
        $html .= '<tr>';
        $html .= '<td>There is no class postings.</td>';
        $html .= '<td></td>';
        $html .= '<td></td>';
        $html .= '<td class="no-message"></td>';
        $html .= '</tr>';
        for ($i = 0; $i < 10; $i++):
            $html .= '<tr>';
            $html .= '<td>&nbsp</td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '</tr>';
        endfor;
    endif;

    echo $html;
    //var_dump($data);
    die;
}
if ($task == "write_post_message") {
    $id = $_REQUEST['id'];
    $subject = $_REQUEST['subject'];
    $message = $_REQUEST['message'];

    $user_id = get_current_user_id();
    $created_on = date('Y-m-d H:i:s', time());

    $data = array(
        'group_id' => $id,
        'posted_by' => $user_id,
        'subject' => $subject,
        'message' => $message,
        'posted_on' => $created_on
    );

    MWDB::insert_group_message($data);

    echo 1;
    die;
}
if ($task == "get_group_students_list") {
    $id = $_REQUEST['gid'];
    $data = MWDB::get_group_students($id);

    $html = "";


    if (count($data) >= 1):
        foreach ($data as $value):
            $html .= '<tr>';
            $html .= '<td>' . $value->display_name . '</td>';
            $html .= '<td>' . $value->user_email . '</td>';
            $html .= '<td>' . $value->joined_date . '</td>';
            $html .= '<td>' . date('Y-m-d', strtotime($value->user_registered)) . '</td>';
            $html .= '<td class="student-post" data-id="' . $value->ID . '" style="width: 50px;border-left: 3px solid #ffffff !important; text-align: center; cursor: pointer;"></td>';
            $html .= '</tr>';
        endforeach;
        for ($i = count($data); $i < 11; $i++):
            $html .= '<tr>';
            $html .= '<td>&nbsp</td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '</tr>';
        endfor;
    else:
        $html .= '<tr>';
        $html .= '<td>There is no students yet.</td>';
        $html .= '<td></td>';
        $html .= '<td></td>';
        $html .= '<td></td>';
        $html .= '<td class="no-message"></td>';
        $html .= '</tr>';
        for ($i = 0; $i < 10; $i++):
            $html .= '<tr>';
            $html .= '<td>&nbsp</td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '</tr>';
        endfor;
    endif;

    echo $html;
    //var_dump($data);
    die;
}
if ($task == "get_option_student_list") {
    $id = $_REQUEST['gid'];
    $data = MWDB::get_group_students($id);

    $html = "";

    if (count($data) >= 1):
        foreach ($data as $value):
            $html .= '<option value="' . $value->ID . '">' . $value->display_name . '</option>';
        endforeach;
    else:
        $html .= '<option value="">No students</option>';
    endif;

    echo $html;
    die;
}
if ($task == "delete_post_message") {
    $id = $_REQUEST['id'];
    $wpdb->delete(
            $wpdb->prefix . 'dict_group_messages', array(
        'id' => $id
            )
    );
    echo 'ok';
    die;
}
if ($task == "get_message_center") {
    $id = $_REQUEST['gid'];

    //Get all message postings
    $postings = MWDB::get_group_messages($id);
    $arr_posting = array();
    if (count($postings) > 0) {
        foreach ($postings as $value) {
            $arr_posting[] = array(
                'id' => $value->id,
                'subject' => $value->subject,
                'student' => $value->student_name,
                'date' => date('Y-m-d / h:i a', strtotime($value->posted_on)),
                'message' => wp_trim_words($value->subject, 8, '...'),
            );
        }
    }

    //Get all students
    $students = MWDB::get_group_students($id);
    $arr_student = array();
    if (count($students) > 0) {
        foreach ($students as $value) {
            $arr_student[] = array(
                'id' => $value->ID,
                'display_name' => $value->display_name,
                'email' => $value->user_email,
                'joined_date' => $value->joined_date,
                'user_registered' => $value->user_registered,
                'done_hw' => $value->homeworks_done
            );
        }
    }

    //Get all receives
    $receives = MWDB::get_message_receive($id);
    $arr_receive = array();
    if (count($receives) > 0) {
        foreach ($receives as $value) {
            $arr_receive[] = array(
                'id' => $value->id,
                'subject' => $value->subject,
                'sender' => $value->display_name,
                'receivedDate' => date('Y-m-d / h:i a', strtotime($value->received_on)),
                'message' => $value->message,
            );
        }
    }

    //Get all sent
    $sents = MWDB::get_message_sent($id);
    $arr_sent = array();
    if (count($sents) > 0) {
        foreach ($sents as $value) {
            $arr_sent[] = array(
                'id' => $value->id,
                'subject' => $value->subject,
                'sender' => $value->display_name,
                'sent_on' => date('Y-m-d / h:i a', strtotime($value->sent_on)),
                'message' => $value->message,
            );
        }
    }

    //Get all assignments
    $is_admin = is_mw_super_admin() || is_mw_admin() ? true : false;
    $current_user_id = get_current_user_id();

    $is_math_panel = is_math_panel();

    $current_page = max(1, get_query_var('page'));
    $filter = get_page_filter_session();
    if (empty($filter) && !isset($_POST['filter'])) {
        $filter['orderby'] = 'g.name';
        $filter['order-dir'] = 'asc';
        $filter['items_per_page'] = 25;
        $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
        $filter['created_by'] = get_current_user_id();
        $filter['subscription_status'] = true;
        if ($is_admin) {
            $filter['fetch_classes'] = true;
        }
    } else {
        $filter['created_by'] = $current_user_id;
        if (isset($_POST['filter']['search'])) {
            $filter['group-name'] = $_REAL_POST['filter']['group-name'];
            $filter['class_type'] = $_POST['filter']['class-types'];
        }

        if (isset($_REAL_POST['filter']['orderby'])) {
            $filter['orderby'] = $_REAL_POST['filter']['orderby'];
            $filter['order-dir'] = $_REAL_POST['filter']['order-dir'];
        }

        $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
    }
    $filter['group_id'] = $id;
    $filter['check_result'] = true;
    $filter['created_by'] = '';
    $filter['offset'] = 0;
    $filter['items_per_page'] = 99999999;
    $assignments = MWDB::get_homework_assignments($filter, $filter['offset'], $filter['items_per_page']);
    $total_pages = ceil($assignments->total / $filter['items_per_page']);

    if (isset($_POST['remove-assignment'])) {
        if (MWDB::remove_homework($_POST['cid']) !== false) {
            wp_redirect(locale_home_url() . '/?r=teachers-box&gid=' . $id);
            exit;
        }
    }

    set_page_filter_session($filter);

    $arr_assignments = array();
    if (count($assignments->items) > 0) {
        foreach ($assignments->items as $assignment) {
            $name = !empty($assignment->name) ? $assignment->name . '<br>' : '';
            $name += sprintf(__('Worksheet: %s', 'iii-dictionary'), $assignment->sheet_name);
            $deadline = $assignment->deadline == '0000-00-00' ? 'None' : ik_date_format($assignment->deadline);

            $arr_assignments[] = array(
                'name' => $name,
                'grade' => $assignment->grade,
                'assigned_date' => ik_date_format($assignment->created_on),
                'deadline' => $deadline,
                'ordering' => $assignment->ordering,
                'sheet_id' => $assignment->sheet_id,
                'id' => $assignment->id,
                'active' => $assignment->active
            );
        }
    }
    echo json_encode(array('postings' => $arr_posting, 'receives' => $arr_receive, 'sents' => $arr_sent, 'students' => $arr_student, 'assignments' => $arr_assignments));
    die;
}
if ($task == "get_math_search") {
    $math_assignments = $_REQUEST['math_assignments'];
    $level_categories = $_REQUEST['level_categories'];
    $homework_types = $_REQUEST['homework_types'];
    $levels = $_REQUEST['levels'];
    $sublevels = $_REQUEST['sublevels'];
    $lang = $_REQUEST['lang'];
    $sheet_name = $_REQUEST['sheet_name'];

    $filter['lang'] = $lang;
    $filter['sheet-name'] = $sheet_name;
    $filter['assignment-id'] = $math_assignments;
    $filter['homework-types'] = $homework_types;
    $filter['cat-level'] = $level_categories;
    $filter['level'] = $levels;
    $filter['sublevel'] = $sublevels;
    $filter['orderby'] = 'ordering';
    $filter['order-dir'] = 'asc';
    $filter['offset'] = 0;
    $filter['items_per_page'] = 99999999;

    $sheets_obj = MWDB::get_math_sheets($filter, $filter['offset'], $filter['items_per_page']);
    $avail_sheets = $sheets_obj->items;
    $html = "";
    if (count($avail_sheets) >= 1) {
        foreach ($avail_sheets as $sheet) {
            if (isset($sheet->active))
                $class = '';
            else
                $class = ' class="text-muted"';

            $html .= '<tr ' . $class . ' data-id="' . $sheet->id . '" data-assignment="' . $sheet->assignment_id . '">';
            $html .= '<td>' . $sheet->level_category_name . '</td>';
            $html .= '<td>' . $sheet->level_name . '</td>';
            $html .= '<td>' . $sheet->sublevel_name . '</td>';
            $html .= '<td>' . $sheet->type . '</td>';
            $html .= '<td>' . $sheet->sheet_name . '</td>';
            $html .= '<td class="op-new-assign"><a class="view-assign-homework" data-toggle="modal" data-target=".bd-assign-modal-lg" data-sheet-name="' . $sheet->sheet_name . '" data-sheet-id="' . $sheet->id . '"><img class="img-height-20" src="' . get_template_directory_uri() . '/library/images/icon_assign.png"></a><a data-toggle="modal" data-target=".bd-preview-modal-lg" style="margin-left: 10px;"><img class="img-height-24"  src="' . get_template_directory_uri() . '/library/images/icon_preview2.png"></a></td>';
            $html .= '</tr>';
        }
        for ($i = count($avail_sheets); $i < 12; $i++):
            $html .= '<tr><td colspan="6">&nbsp&nbsp</td></tr>';
        endfor;
    }else {
        $html .= '<tr>';
        $html .= '<td colspan="5">No results</td>';
        $html .= '<td class="op-new-assign" style="background: #dfdfdf; height: auto;"><a><img class="img-height-20" src="' . get_template_directory_uri() . '/library/images/icon_assign_gray.png"></a><a style="margin-left: 10px;"><img class="img-height-24"  src="' . get_template_directory_uri() . '/library/images/icon_preview2_gray.png"></a>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '<tr><td colspan="6">&nbsp&nbsp</td></tr>';
        $html .= '<tr><td colspan="6">&nbsp&nbsp</td></tr>';
        $html .= '<tr><td colspan="6">&nbsp&nbsp</td></tr>';
        $html .= '<tr><td colspan="6">&nbsp&nbsp</td></tr>';
        $html .= '<tr><td colspan="6">&nbsp&nbsp</td></tr>';
        $html .= '<tr><td colspan="6">&nbsp&nbsp</td></tr>';
        $html .= '<tr><td colspan="6">&nbsp&nbsp</td></tr>';
        $html .= '<tr><td colspan="6">&nbsp&nbsp</td></tr>';
        $html .= '<tr><td colspan="6">&nbsp&nbsp</td></tr>';
        $html .= '<tr><td colspan="6">&nbsp&nbsp</td></tr>';
        $html .= '<tr><td colspan="6">&nbsp&nbsp</td></tr>';
    }
    echo $html;
    //var_dump($data);
    die;
}
if ($task == "create_account") {
    $user_name = $_REQUEST['user_name'];
    $user_password = $_REQUEST['user_password'];
    $confirm_password = $_REQUEST['confirm_password'];
    $first_name = $_REQUEST['first_name'];
    $last_name = $_REQUEST['last_name'];
    $birth_m = $_REQUEST['birth_m'];
    $birth_d = $_REQUEST['birth_d'];
    $birth_y = $_REQUEST['birth_y'];
    $cb_lang = $_REQUEST['cb_lang'];
    $profile_avatar = $_REQUEST['profile_avatar'];

    $html = '';
    $form_valid = true;
    if (is_email($user_name)) {
        if (email_exists($user_name) || username_exists($user_name)) {
            $html .= '<div class="error-message">';
            $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>:' . __('This email address is already registered. Please choose another one.', 'iii-dictionary');
            $html .= '</div>';
            $form_valid = false;
        }
        $user_email = $user_name;
    } else {
        // we don't accept normal string as username anymore
        $html .= '<div class="error-message">';
        $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>:' . __('This email address is invalid. Please choose another one.', 'iii-dictionary');
        $html .= '</div>';
        $form_valid = false;
    }

    if (trim($user_password) == '') {
        $html .= '<div class="error-message">';
        $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>:' . __('Passwords must not be empty', 'iii-dictionary');
        $html .= '</div>';
        $form_valid = false;
    }

    if ($user_password !== $confirm_password) {
        $html .= '<div class="error-message">';
        $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>:' . __('Passwords must match', 'iii-dictionary');
        $html .= '</div>';
        $form_valid = false;
    }

    if (strlen($user_password) < 6) {
        $html .= '<div class="error-message">';
        $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>:' . __('Passwords must be at least six characters long', 'iii-dictionary');
        $html .= '</div>';
        $form_valid = false;
    }

    if ($birth_y != '' && is_numeric($birth_y)) {
        if (checkdate($birth_m, $birth_d, $birth_y)) {
            $date_of_birth = $birth_m . '/' . $birth_d . '/' . $birth_y;
        } else {
            $html .= '<div class="error-message">';
            $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>:' . __('Invalid date of birth', 'iii-dictionary');
            $html .= '</div>';
            $form_valid = false;
        }
    } else {
        $date_of_birth = '';
    }

    if ($form_valid) {
        if (isset($user_email)) {
            $user_id = wp_create_user($user_name, $user_password, $user_email);
        } else {
            $user_id = wp_create_user($user_name, $user_password);
        }

        //$userdata['ID'] = $user_id;

        if (isset($first_name) && trim($first_name) != '') {
            update_user_meta($user_id, 'first_name', $first_name);
        }

        if (isset($last_name) && trim($last_name) != '') {
            update_user_meta($user_id, 'last_name', $last_name);
        }

        if (isset($first_name) && trim($first_name) != '' && isset($last_name) && trim($last_name) != '') {
            $display_name = $first_name . ' ' . $last_name;
            update_user_meta($user_id, 'display_name', $display_name);
        }

        if (count($cb_lang) > 0) {
            $language_type = implode(',', $cb_lang);
            update_user_meta($user_id, 'language_type', $language_type);
        }

        if (isset($profile_avatar) && trim($profile_avatar) != '') {
            update_user_meta($user_id, 'ik_user_avatar', $profile_avatar);
        }

        update_user_meta($user_id, 'date_of_birth', $date_of_birth);

        // auto login the user
        $creds['user_login'] = $user_name;
        $creds['user_password'] = $user_password;
        $user = wp_signon($creds, false);

        // send confirmation email
        if (is_email($user_email)) {
            $title = __('Congratulations! You have successfully signed up for iklearn.com', 'iii-dictionary');
            $message = __('If you have questions or need support, please contact us at support@iktutor.com', 'iii-dictionary') . "\r\n\r\n" .
                    __('If you forgot your password, please click on the "forgot password" button after entering your username (email address).', 'iii-dictionary') . "\r\n\r\n" .
                    __('If you are student, you can take online course, or get a live tutor online.', 'iii-dictionary') . "\r\n\r\n\r\n" .
                    __('If you are registered as a tutor, you can prove helps to students in learning in school and homework.', 'iii-dictionary') . "\r\n\r\n\r\n" .
                    __('Enjoy!');

            wp_mail($user_email, wp_specialchars_decode($title), $message);
        }

        $_SESSION['newuser'] = 1;
        echo '1';
    } else {
        echo $html;
    }
    exit();
}
if ($task == "update_info") {

    $user_email = $_REQUEST['user_email'];
    $new_password = $_REQUEST['new_password'];
    $retype_new_password = $_REQUEST['retype_new_password'];
    $current_password = $_REQUEST['current_password'];
    $mobile_number = $_REQUEST['mobile_number'];
    $last_school = $_REQUEST['last_school'];
    $previous_school = $_REQUEST['previous_school'];
    $skype_id = $_REQUEST['skype_id'];
    $user_profession = $_REQUEST['user_profession'];
    $cb_lang = $_REQUEST['cb_lang'];
    $profile_avatar = $_REQUEST['profile_avatar'];

    $html = '';
    $form_valid = true;
    $current_user = wp_get_current_user();
    if (is_email($user_email) && trim($user_email) != '') {
        if (email_exists($user_email)) {
            $html .= '<div class="error-message">';
            $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>:' . __('This email address is already registered. Please choose another one.', 'iii-dictionary');
            $html .= '</div>';
            $form_valid = false;
        }
    }

    if (trim($new_password) != '') {
        if (trim($current_password) == '' || trim($retype_new_password) == '') {
            $html .= '<div class="error-message">';
            $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>:' . __('Passwords must not be empty', 'iii-dictionary');
            $html .= '</div>';
            $form_valid = false;
        }
    }

    if ($new_password !== $retype_new_password) {
        $html .= '<div class="error-message">';
        $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>:' . __('Passwords must match', 'iii-dictionary');
        $html .= '</div>';
        $form_valid = false;
    }

    if (trim($new_password) != '' && strlen($new_password) < 6) {
        $html .= '<div class="error-message">';
        $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>:' . __('Passwords must be at least six characters long', 'iii-dictionary');
        $html .= '</div>';
        $form_valid = false;
    }

    if (trim($new_password) != '' && !wp_check_password($current_password, $current_user->user_pass)) {
        $html .= '<div class="error-message">';
        $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>:' . __('Current password not match.', 'iii-dictionary');
        $html .= '</div>';
        $form_valid = false;
    }
    if ($form_valid) {

        if (trim($user_email) != '' || trim($new_password) != '') {
            $userdata = array('ID' => $current_user->ID, 'user_pass' => $new_password, 'user_email' => $user_email);

            wp_update_user($userdata);
        }

        if (isset($mobile_number) && trim($mobile_number) != '') {
            update_user_meta($current_user->ID, 'mobile_number', $mobile_number);
        }

        if (isset($last_school) && trim($last_school) != '') {
            update_user_meta($current_user->ID, 'last_school', $last_school);
        }

        if (isset($previous_school) && trim($previous_school) != '') {
            update_user_meta($current_user->ID, 'previous_school', $previous_school);
        }

        if (isset($skype_id) && trim($skype_id) != '') {
            update_user_meta($current_user->ID, 'skype_id', $skype_id);
        }

        if (isset($user_profession) && trim($user_profession) != '') {
            update_user_meta($current_user->ID, 'user_profession', $user_profession);
        }

        if (isset($profile_avatar) && trim($profile_avatar) != '') {
            update_user_meta($current_user->ID, 'ik_user_avatar', $profile_avatar);
        }
        if (isset($cb_lang)) {
            if (count($cb_lang) > 0) {
                $language_type = implode(',', $cb_lang);
                update_user_meta($current_user->ID, 'language_type', $language_type);
            }
        }
        echo 1;
    } else {
        echo $html;
    }
    exit();
}
if ($task == "upload_avatar") {

    $file = $_FILES['file'];
    $current_user = wp_get_current_user();
    if (isset($current_user->user_login)) {
        $user_dir = $current_user->user_login;
    } else {
        $user_dir = 'avatar';
    }
    if ($file['error'] == UPLOAD_ERR_OK) {
        $allowedTypes = array('image/png', 'image/jpeg', 'image/gif');
        $error = !in_array($file['type'], $allowedTypes);
        if (!$error) {
            $wp_upload_dir = wp_upload_dir();
            $avatar_file_name = str_replace(' ', '_', $file['name']);
            $upload_dir = $wp_upload_dir['basedir'] . '/' . $user_dir . '/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir);
            }

            if (move_uploaded_file($file['tmp_name'], $upload_dir . $avatar_file_name)) {
                $avatar_url = $wp_upload_dir['baseurl'] . '/' . $user_dir . '/' . $avatar_file_name;
                echo $avatar_url;
            } else {
                echo '0';
            }
        } else {
            echo '0';
        }
    } else {
        echo '0';
    }
    die;
}
if ($task == "credit_code") {
    $credit_code = trim($_REQUEST['credit_code']);
    $starting_date_txt = $_REQUEST['starting_date_txt'];
    $dictionary_id = $_REQUEST['dictionary_id'];
    $assoc_group = $_REQUEST['assoc_group'];
    $group_name = $_REQUEST['group_name'];
    $group_pass = $_REQUEST['group_pass'];
    if ($credit_code == '')
        $credit_code = trim($_REQUEST['activation-code']);

    $html = '';
    $has_err = false;

    $code = MWDB::get_credit_code($credit_code);
    if (is_null($code)) {
        $html .= '<div class="error-message">';
        $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>:' . __('Invalid code number.', 'iii-dictionary');
        $html .= '</div>';
        $has_err = true;
    }

    if ($starting_date_txt == '') {
        $html .= '<div class="error-message">';
        $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>:' . __('Invalid starting date.', 'iii-dictionary');
        $html .= '</div>';
        $has_err = true;
    }

    if ($has_err) {
        $has_err = false;
        echo $html;
        die();
    }

    $user = wp_get_current_user();

    // Homework Tool
    if ($code->typeid == 1 || $code->typeid == 6) {
        $no_of_months = $code->no_of_months_teacher_tool;

        // check to see if user select an existing group or want to create new group
        if ($assoc_group != '' && $group_name == '') {
            // user selected a group
            $group_id = $assoc_group;
        } else {
            // no group id, check if user enter group name
            // create new group
            if ($group_name != '' && $group_pass != '') {
                $group_id = MWDB::store_group(array('gname' => $group_name, 'gpass' => $group_pass));

                if (!$group_id) {
                    $html .= '<div class="error-message">';
                    $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>:' . __('Cannot create group.', 'iii-dictionary');
                    $html .= '</div>';
                    $has_err = true;
                }
            } else {
                $html .= '<div class="error-message">';
                $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>:' . __('Invalid group name/password.', 'iii-dictionary');
                $html .= '</div>';
                $has_err = true;
            }
        }
    }
    // Dictionary
    else if ($code->typeid == 2 || $code->typeid == 9) {
        $group_id = 0;
        $no_of_months = $code->no_of_months_dictionary;

        // check to see if user can still add this code
        $result = $wpdb->get_col('SELECT COUNT(*) 
								  FROM ' . $wpdb->prefix . 'dict_user_subscription
								  WHERE activation_code_id = ' . $code->id);

        if (!empty($result) && $result[0] >= $code->no_of_students) {
            // max number of activation reached
            $html .= '<div class="error-message">';
            $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>:' . __('Number of license is used up for this activation code.', 'iii-dictionary');
            $html .= '</div>';
            $has_err = true;
        }
    }
    //Point 
    else if ($code->typeid == 4) {
        $cur_points = ik_get_user_points();
        $cur_points += $code->num_points;
        update_user_meta($user->ID, 'user_points', $cur_points);
    }
    // SAT Preparation		
    else {
        $no_of_months = $code->no_of_months_sat;
    }

    // does user has to choose dictionary for this code
    if (!$code->dictionary_id && $code->typeid != 3) {
        if ($dictionary_id != '') {
            $row_data['dictionary_id'] = $dictionary_id;
            $dictionary_id = $dictionary_id;
        } else {
            $html .= '<div class="error-message">';
            $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>:' . __('Please choose a Dictionary.', 'iii-dictionary');
            $html .= '</div>';
            $has_err = true;
        }
    } else {
        $dictionary_id = $code->dictionary_id;
    }

    // finish validating
    // calculate expired date
    $starting_date = date('Y-m-d', strtotime($starting_date_txt));
    $expired_date = date('Y-m-d', strtotime('+' . $no_of_months . ' months', strtotime($starting_date)));

    if (!$has_err) {
        if (!empty($row_data['dictionary_id'])) {
            $result = $wpdb->update(
                    $wpdb->prefix . 'dict_credit_codes', $row_data, array('id' => $code->id)
            );
        }

        // store user's subscription
        $sub_data['activation_code_id'] = $code->id;
        $sub_data['user_id'] = $user->ID;
        $sub_data['starting_date'] = $starting_date;
        $sub_data['expired_date'] = $expired_date;
        $sub_data['code_typeid'] = $code->typeid;
        $sub_data['group_id'] = $group_id;
        $sub_data['sat_class_id'] = 0;
        $sub_data['number_of_students'] = $code->no_of_students;
        $sub_data['number_of_months'] = $no_of_months;
        $sub_data['dictionary_id'] = $dictionary_id;

        if (!empty($code->sat_class_id)) {
            $sub_data['sat_class_id'] = $code->sat_class_id;
        }

        $subscription_id = MWDB::add_user_subscription($sub_data);

        if ($subscription_id) {
            // update subscription status
            update_user_subscription();
            //ik_enqueue_messages(__('Successfully add subscription.', 'iii-dictionary'), 'success');
            //return $subscription_id;
            echo '1';
            die;
        } else {
            $html .= '<div class="error-message">';
            $html .= '<strong>' . __('Error', 'iii-dictionary') . '</strong>:' . __('Cannot add subscription.', 'iii-dictionary');
            $html .= '</div>';
        }
    }
    echo $html;
    die;
}
if ($task == 'show_eng_tab') {

    // process task
    $data = array();
    $data['assignment-id'] = ASSIGNMENT_SPELLING;

    // update or create english sheet
    if (isset($task['create']) || isset($task['update'])) {
        $data['id'] = $_REAL_POST['sid'];
        $data['assignment-id'] = $_REAL_POST['assignments'];
        $data['homework-types'] = $_REAL_POST['homework-types'];
        $data['sheet-categories'] = $_REAL_POST['sheet-categories'];
        $data['trivia-exclusive'] = isset($_REAL_POST['trivia-exclusive']) ? $_REAL_POST['trivia-exclusive'] : 0;
        $data['grade'] = $_REAL_POST['grade'];
        $data['sheet-name'] = $_REAL_POST['sheet-name'];
        $data['grading-price'] = $_REAL_POST['grading-price'];
        $data['dictionary'] = $_REAL_POST['dictionary'];
        $data['questions'] = $_REAL_POST['words'];
        $data['reading_passage'] = $_REAL_POST['reading_passage'];
        $data['description'] = $_REAL_POST['description'];
        $data['wordchecked'] = $_REAL_POST['wordchecked'];
        $data['active'] = 0; // disable sheet by default
        $data['next-worksheet-id'] = $_REAL_POST['next-worksheet-id'];
        $data['lang'] = !empty($_POST['lang']) ? $_POST['lang'] : 'en';

        if (MWDB::store_sheet($data)) {
            wp_redirect(home_url() . '/?r=admin-homework-creator/english');
            exit;
        } else {
            /* if($_REAL_POST['sid']) {
              wp_redirect(home_url() . '/?r=admin-homework-creator/english&layout=create&cid=' . $_REAL_POST['sid']);
              exit;
              } */
        }
    }

    // remove sheet
    if (isset($task['remove'])) {
        $cid = $_REAL_POST['cid'];

        if (MWDB::delete_sheets($cid)) {
            wp_redirect(home_url() . '/?r=admin-homework-creator/english');
            exit;
        }
    }

    // export all sheets to CSV file
    if (isset($_POST['export'])) {
        $slist = MWDB::get_all_sheets();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=homework_export_' . date('mdY_Hms', time()));
        $fp = fopen('php://output', 'w');
        foreach ($slist as $item) {
            $row_header = array('Sheet Name: ' . $item->sheet_name . ' --- Grade: ' . $item->grade);
            fputcsv($fp, $row_header);
            $content = json_decode($item->questions);
            if ($item->assignment_id == ASSIGNMENT_SPELLING) {
                foreach ($content as $item) {
                    fputcsv($fp, array(html_entity_decode($item, ENT_QUOTES)));
                }
            } else {
                foreach ($content->question as $key => $value) {
                    $col1 = html_entity_decode($content->quiz[$key], ENT_QUOTES);
                    $col2 = html_entity_decode($content->question[$key], ENT_QUOTES);
                    $col3 = html_entity_decode($content->c_answer[$key], ENT_QUOTES);
                    $col4 = html_entity_decode($content->w_answer1[$key], ENT_QUOTES);
                    $col5 = html_entity_decode($content->w_answer2[$key], ENT_QUOTES);

                    $row = array($col1, $col2, $col3, $col4, $col5);
                    if (!empty($content->w_answer3[$key])) {
                        $row[] = html_entity_decode($content->w_answer3[$key], ENT_QUOTES);
                    }
                    if (!empty($content->w_answer4[$key])) {
                        $row[] = html_entity_decode($content->w_answer4[$key], ENT_QUOTES);
                    }

                    fputcsv($fp, $row);

                    if ($item->assignment_id == ASSIGNMENT_READING) {
                        fputcsv($fp, array(strip_tags($item->passages)));
                    }
                }
            }
            fputcsv($fp, array());
            fputcsv($fp, array());
        }
        fclose($fp);
        exit;
    }

    // page content
    if ($cid) { // view a sheet
        $current_sheet = $wpdb->get_row($wpdb->prepare(
                        'SELECT s.*, gr.name AS grade
					FROM ' . $wpdb->prefix . 'dict_sheets AS s
					JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = s.grade_id
					WHERE s.id = %s', $cid
        ));

        $data['assignment-id'] = $current_sheet->assignment_id;
        $data['homework-types'] = $current_sheet->homework_type_id;
        $data['sheet-categories'] = $current_sheet->category_id;
        $data['trivia-exclusive'] = $current_sheet->trivia_exclusive;
        $data['grade'] = $current_sheet->grade_id;
        $data['sheet-name'] = $current_sheet->sheet_name;
        $data['dictionary'] = $current_sheet->dictionary_id;
        $data['questions'] = json_decode($current_sheet->questions, true);
        $data['reading_passage'] = $current_sheet->passages;
        $data['description'] = $current_sheet->description;
        $data['lang'] = $current_sheet->lang;
    } else { // sheet list
        $current_page = max(1, get_query_var('page'));
        $filter = get_page_filter_session();
        if (empty($filter) && !isset($_REAL_POST['filter'])) {
            $filter['orderby'] = 'grade';
            $filter['order-dir'] = 'asc';
            $filter['items_per_page'] = 20;
            $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
        } else {
            if (isset($_REAL_POST['filter']['search'])) {
                $filter['lang'] = $_POST['filter']['lang'];
                $filter['sheet-name'] = $_REAL_POST['filter']['sheet-name'];
                $filter['grade'] = $_REAL_POST['filter']['grade'];
                $filter['assignment-id'] = $_REAL_POST['filter']['assignment-id'];
                $filter['homework-types'] = $_REAL_POST['filter']['homework-types'];
                $filter['trivia-exclusive'] = $_REAL_POST['filter']['trivia-exclusive'];
                $filter['active'] = $_REAL_POST['filter']['active'];
            }

            if (isset($_REAL_POST['filter']['orderby'])) {
                $filter['orderby'] = $_REAL_POST['filter']['orderby'];
                $filter['order-dir'] = $_REAL_POST['filter']['order-dir'];
            }

            $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
        }

        set_page_filter_session($filter);
        $filter['offset'] = 0;
        $filter['items_per_page'] = 99999999;
        $sheets_obj = MWDB::get_sheets($filter, false, true);

        $avail_sheets = $sheets_obj->items;
        $total_rows = $sheets_obj->total;
        $total_pages = ceil($total_rows / $filter['items_per_page']);
        $pagination = paginate_links(array(
            'format' => '?page=%#%',
            'current' => $current_page,
            'total' => $total_pages
        ));
        $arr = array();
        if (count($avail_sheets) > 0) {
            foreach ($avail_sheets as $value) {
                $arr[] = array(
                    'id' => $value->id,
                    'sheet_name' => $value->sheet_name,
                    'assignment' => $value->assignment,
                    'name' => $value->name,
                    'assignment_id' => $value->assignment_id,
                    'description' => $value->description
                );
            }
        }

        echo json_encode(array('engsheet' => $arr));
        die;
    }
}
if ($task == 'show_math_tab') {
    $html = "";
    /* mathematic */


    // create or update a worksheet
    if (isset($task['create']) || isset($task['update'])) {
        $data['assignment_id'] = $_POST['math-assignments'];
        $data['homework_type_id'] = $_POST['homework-types'];
        $data['grade_id'] = $_POST['sublevel'];
        $data['sheet_name'] = $_REAL_POST['sheet-name'];
        $data['questions'] = $_REAL_POST['questions'];
        $data['description'] = $_REAL_POST['description'];
        $data['answer_time_limit'] = $_POST['answer-time-limit'];
        $data['show_answer_after'] = $_POST['show-answer-after'];
        $data['category_id'] = 5; // Set to Math category
        $data['active'] = 1;
        $data['created_on'] = date('Y-m-d', time());
        $data['lang'] = $_POST['lang'];
        switch ($data['assignment_id']) {
            case MATH_ASSIGNMENT_SINGLE_DIGIT_DIV:
            case MATH_ASSIGNMENT_TWO_DIGIT_DIV:
                $data['questions']['sign'] = '&divide;';
                $steps = explode("\r\n", $data['questions']['steps']);
                foreach ($steps as $key => $v) {
                    $data['questions']['step']['s' . ($key + 1)] = $v;
                }
                $total_step = count($steps);
                $data['questions']['step']['s' . ($total_step + 1)] = $data['questions']['remainder'];
                $data['questions']['step']['s' . ($total_step + 2)] = $data['questions']['answer'];
                break;

            case MATH_ASSIGNMENT_FLASHCARD:
            case MATH_ASSIGNMENT_FRACTION:
            case MATH_ASSIGNMENT_EQUATION:
                foreach ($data['questions']['q'] as $key => $item) {
                    $data['questions']['q'][$key]['op'] = htmlentities($item['op']);
                    if (trim($item['answer']) == '') {
                        unset($data['questions']['q'][$key]);
                    }
                }
                break;

            case MATH_ASSIGNMENT_WORD_PROB:
                foreach ($data['questions']['q'] as $key => $item) {
                    if (empty($item['image']) || trim($item['image']) == '') {
                        unset($data['questions']['q'][$key]);
                    }
                }
                break;
        }

        if (!empty($_POST['cid'])) {
            $data['id'] = $_POST['cid'];
        } else {
            $data['created_by'] = get_current_user_id();

            $hightest_order = $wpdb->get_col(
                    $wpdb->prepare('SELECT MAX(ordering) FROM ' . $wpdb->prefix . 'dict_sheets WHERE grade_id = %d', $data['grade_id'])
            );
            $data['ordering'] = $hightest_order[0] + 1;
        }

        $sel_level_category = $_POST['level-category'];
        $sel_level = $_POST['level'];

        if (MWDB::store_math_sheet($data)) {
            wp_redirect(home_url() . '/?r=admin-homework-creator/mathematics');
            exit;
        }
    }

    // change sheet order up
    if (isset($_POST['order-up'])) {
        MWDB::set_math_sheet_order_up($_POST['oid']);
        wp_redirect(locale_home_url() . '/?r=admin-homework-creator/mathematics');
        exit;
    }

    // change sheet order down
    if (isset($_POST['order-down'])) {
        MWDB::set_math_sheet_order_down($_POST['oid']);
        wp_redirect(locale_home_url() . '/?r=admin-homework-creator/mathematics');
        exit;
    }

    // toggle active a sheet
    if (isset($task['active'])) {
        $cid = $_REAL_POST['cid'];

        if (!empty($cid)) {
            if (MWDB::toggle_active_math_sheets($cid)) {
                ik_enqueue_messages('Successfully active/deactive ' . count($cid) . ' sheets', 'success');

                wp_redirect(home_url() . '/?r=admin-homework-creator/mathematics');
                exit;
            }
        } else {
            ik_enqueue_messages('Please select a sheet.', 'error');
        }
    }

    // delete math sheet
    if (isset($task['remove'])) {
        $cid = $_REAL_POST['cid'];

        if (MWDB::delete_math_sheets($cid)) {
            wp_redirect(home_url() . '/?r=admin-homework-creator/mathematics');
            exit;
        }
    }

    $main_categories = MWDB::get_grades(array('type' => 'MATH', 'level' => 0, 'admin_only' => 1, 'orderby' => 'ordering', 'order-dir' => 'asc'));
    $levels = MWDB::get_grades(array('type' => 'MATH', 'level' => 1, 'orderby' => 'ordering', 'order-dir' => 'asc'));
    $sublevels = MWDB::get_grades(array('type' => 'MATH', 'level' => 2, 'orderby' => 'ordering', 'order-dir' => 'asc'));

    // page content
    if ($cid) { // view a sheet
        $current_sheet = MWDB::get_math_sheet_by_id($cid);

        $data['assignment_id'] = $current_sheet->assignment_id;
        $data['homework_type_id'] = $current_sheet->homework_type_id;
        $data['sublevel_id'] = $current_sheet->grade_id;
        $data['sheet_name'] = $current_sheet->sheet_name;
        $data['questions'] = json_decode($current_sheet->questions, true);
        $data['description'] = $current_sheet->description;
        $data['answer_time_limit'] = $current_sheet->answer_time_limit;
        $data['show_answer_after'] = $current_sheet->show_answer_after;
        $sel_level_category = $current_sheet->category_level_id;
        $sel_level = $current_sheet->level_id;
        $data['lang'] = $current_sheet->lang;
    } else { // sheet list
        $current_page = max(1, get_query_var('page'));
        $filter = get_page_filter_session();
        if (empty($filter) && !isset($_REAL_POST['filter'])) {
            $filter['orderby'] = 'ordering';
            $filter['order-dir'] = 'asc';
            $filter['items_per_page'] = 20;
            $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
        } else {
            if (isset($_REAL_POST['filter']['search'])) {
                $filter['lang'] = $_POST['filter']['lang'];
                $filter['sheet-name'] = $_REAL_POST['filter']['sheet-name'];
                $filter['assignment-id'] = $_REAL_POST['filter']['math-assignments'];
                $filter['homework-types'] = $_REAL_POST['filter']['homework-types'];
                $filter['active'] = $_REAL_POST['filter']['active'];
                $filter['cat-level'] = $_REAL_POST['filter']['cat-level'];
                $filter['level'] = $_REAL_POST['filter']['level'];
                $filter['sublevel'] = $_REAL_POST['filter']['sublevel'];
            }

            if (isset($_REAL_POST['filter']['orderby'])) {
                $filter['orderby'] = $_REAL_POST['filter']['orderby'];
                $filter['order-dir'] = $_REAL_POST['filter']['order-dir'];
            }

            $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
        }

        set_page_filter_session($filter);
        $filter['offset'] = 0;
        $filter['items_per_page'] = 99999999;
        $sheets_obj_math = MWDB::get_math_sheets($filter, $filter['offset'], $filter['items_per_page']);
        $avail_sheets = $sheets_obj_math->items;
        $total_rows = $sheets_obj_math->total;
        $total_pages = ceil($total_rows / $filter['items_per_page']);
        $pagination = paginate_links(array(
            'format' => '?page=%#%',
            'current' => $current_page,
            'total' => $total_pages
        ));
        $arr = array();
        if (count($avail_sheets) > 0) {
            foreach ($avail_sheets as $value) {
                $arr[] = array(
                    'id' => $value->id,
                    'sheet_name' => $value->sheet_name,
                    'assignment' => $value->level_category_name,
                    'description' => $value->description,
                    'name' => $value->name,
                    'assignment_id' => $value->assignment_id
                );
            }
        }

        echo json_encode(array('mathsheet' => $arr));
        die;
        // end mathematics
    }
}

if ($task == 'get_sheet') {
    $filter = get_page_filter_session();
    if (empty($filter) && !isset($_REAL_POST['filter'])) {
        $filter['orderby'] = 'grade';
        $filter['order-dir'] = 'asc';
        $filter['items_per_page'] = 20;
        $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
    } else {
        if (isset($_REAL_POST['filter']['search'])) {
            $filter['lang'] = $_POST['filter']['lang'];
            $filter['sheet-name'] = $_REAL_POST['filter']['sheet-name'];
            $filter['group-name'] = $_REAL_POST['filter']['group-name'];
            $filter['grade'] = $_REAL_POST['filter']['grade'];
            $filter['assignment-id'] = $_REAL_POST['filter']['assignment-id'];
            $filter['homework-types'] = $_REAL_POST['filter']['homework-types'];
            $filter['trivia-exclusive'] = $_REAL_POST['filter']['trivia-exclusive'];
            $filter['active'] = $_REAL_POST['filter']['active'];
            $check_global = 1;
        }

        if (isset($_REAL_POST['filter']['orderby'])) {
            $filter['orderby'] = $_REAL_POST['filter']['orderby'];
            $filter['order-dir'] = $_REAL_POST['filter']['order-dir'];
        }

        $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
    }

    set_page_filter_session($filter);
    $filter['offset'] = 0;
    $filter['items_per_page'] = 9999;
    $sheet = MWDB::get_sheets($filter, false, true);
    $avail_sheets = $sheet->items;
    $arr = array();
    if (count($avail_sheets) > 0) {
        foreach ($avail_sheets as $value) {
            $arr[] = array(
                'id' => $value->id,
                'type' => $value->type,
                'grade' => $value->grade,
                'sheet_name' => $value->sheet_name,
                'assignment' => $value->assignment,
                'description' => $value->description
            );
        }
    }

    echo json_encode(array('lesson' => $arr));
    die;
}

if ($task == 'get_worksheets') {
    $lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : '';
    $sheet_name = isset($_REQUEST['sheet_name']) ? $_REQUEST['sheet_name'] : '';
    $group_name = isset($_REQUEST['group_name']) ? $_REQUEST['group_name'] : '';
    $assignment_id = isset($_REQUEST['assignment_id']) ? $_REQUEST['assignment_id'] : '';
    $homework_types = isset($_REQUEST['homework_types']) ? $_REQUEST['homework_types'] : '';
    $grade = isset($_REQUEST['grade']) ? $_REQUEST['grade'] : '';
    $trivia_exclusive = isset($_REQUEST['trivia_exclusive']) ? $_REQUEST['trivia_exclusive'] : '';
    $active = isset($_REQUEST['active']) ? $_REQUEST['active'] : '';
    $cat_level = isset($_REQUEST['cat_level']) ? $_REQUEST['cat_level'] : '';
    $level = isset($_REQUEST['level']) ? $_REQUEST['level'] : '';
    $sublevel = isset($_REQUEST['sublevel']) ? $_REQUEST['sublevel'] : '';
    $type = isset($_REQUEST['type']);
    $current_page = $_REQUEST['page'];
    $number_pub=$_REQUEST['number'];

    if ($type != '') {
        $filter['lang'] = $lang;
        $filter['group-name'] = $group_name;
        $filter['sheet-name'] = $sheet_name;
        $filter['assignment-id'] = $assignment_id;
        $filter['homework-types'] = $homework_types;
        $filter['active'] = $active;
        $filter['cat-level'] = $cat_level;
        $filter['level'] = $level;
        $filter['sublevel'] = $sublevel;
        $filter['grade'] = $grade;
        $filter['trivia-exclusive'] = $trivia_exclusive;
    }

    if ($type == 'english') {
        $filter['orderby'] = 'grade';
    } else {
        $filter['orderby'] = 'active';
    }
    $totalrow = MWDB::get_sheets($filter, false, true);
    $count = (int) $totalrow->total;
    if ($current_page == NULL) {
        $current_page = 1;
    }

    $filter['order-dir'] = 'asc';
    $filter['items_per_page'] = 40;
    $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
    $page = ceil($count / $filter['items_per_page']);
    $sheet_english = MWDB::get_sheets($filter, false, true);
    $arr_english = array();
    $paginate = '';
    $next = $current_page + 1;
    $prev = $current_page - 1;
    if($number_pub=="2"){
         if ($current_page == 1)
        $paginate .= '<span class="link disabled first style-paginate"><</span>';
    else
        $paginate .= '<a class="link first paginate-pub2 style-paginate" page="' . $prev . '" ><</a>';

    if ($current_page < $page)
        $paginate .= '<a  class="link paginate-pub2 style-paginate" page="' . $next . '" >></a>';
    else {
        $paginate .= '<span class="link disabled style-paginate">></span>';
    }
    }else{
    if ($current_page == 1)
        $paginate .= '<span class="link disabled first style-paginate"><</span>';
    else
        $paginate .= '<a class="link first paginate-pub style-paginate" page="' . $prev . '" ><</a>';

    if ($current_page < $page)
        $paginate .= '<a  class="link paginate-pub style-paginate" page="' . $next . '" >></a>';
    else {
        $paginate .= '<span class="link disabled style-paginate">></span>';
    }
    }
    $paginate .= '<span class="">' . $current_page . '/' . $page . '</span>';
    if (count($sheet_english->items) > 0) {
        foreach ($sheet_english->items as $value) {
            if (isset($value->active))
                $class = '';
            else
                $class = 'text-muted';

            $arr_english[] = array(
                'id' => $value->id,
                'type' => $value->type,
                'grade' => $value->grade,
                'sheet_name' => $value->sheet_name,
                'assignment' => $value->assignment,
                'homework_type' => $value->homework_type,
                'class' => $class,
                'description' => $value->description,
                'name' => $value->name,
                'cate_id' => $value->category_id,
            );
        }
    }
    $totalrow_math = MWDB::get_math_sheets($filter, $filter['offset'], $filter['items_per_page']);
    ;
    $count_math = (int) $totalrow_math->total;


    $page_math = ceil($count_math / $filter['items_per_page']);

    $paginate_math = '';

    if ($current_page == 1)
        $paginate_math .= '<span class="link disabled first style-paginate"><</span>';
    else
        $paginate_math .= '<a class="link first paginate-pub style-paginate" page="' . $prev . '" ><</a>';

    if ($current_page < $page)
        $paginate_math .= '<a  class="link paginate-pub style-paginate" page="' . $next . '" >></a>';
    else {
        $paginate_math .= '<span class="link disabled style-paginate">></span>';
    }
    $paginate_math .= '<span class="">' . $current_page . '/' . $page . '</span>';

    $sheets_math = MWDB::get_math_sheets($filter, $filter['offset'], $filter['items_per_page']);
    $arr_math = array();
    if (count($sheets_math->items) > 0) {
        foreach ($sheets_math->items as $value) {
            if (isset($value->active))
                $class = '';
            else
                $class = 'text-muted';

            $arr_math[] = array(
                'id' => $value->id,
                'level_category_name' => $value->level_category_name,
                'level_name' => $value->level_name,
                'sheet_name' => $value->sheet_name,
                'assignment' => $value->assignment,
                'sublevel_name' => $value->sublevel_name,
                'class' => $class,
                'type' => $value->type,
                'description' => $value->description,
                'name' => $value->name,
                'cate_id' => $value->category_id
            );
        }
    }

    echo json_encode(array('math' => $arr_math, 'english' => $arr_english, 'paginate' => $paginate, 'paginate_math' => $paginate_math));
    die;
}

//insert my library
if ($task == 'add_my_library') {
    $arr = $_REQUEST["data"];
    $user = get_current_user_id();
    $a = array();
    $qr = "SELECT id FROM " . $wpdb->prefix . "dict_my_library WHERE created_by=$user";
    $rs = $wpdb->get_var($qr);
    if ($rs == null) {
        $result = $wpdb->insert(
                $wpdb->prefix . 'dict_my_library', array(
            'created_by' => $user,
            'created_on' => date('Y-m-d H:i:s')
                )
        );
    }

    if ($rs !== null) {

        foreach ($arr as $key => $value):
            $a['cate'] = $value['cate'];
            $a['id'] = $value['id'];
            $query2 = "SELECT sheet_id FROM " . $wpdb->prefix . "dict_my_library_sheet WHERE sheet_id=" . $a['id'];
            $exst = $wpdb->get_var($query2);
            $i = 0;
            if ($exst == null) {
                $result2 = $wpdb->insert(
                        $wpdb->prefix . 'dict_my_library_sheet', array(
                    'library_id' => $rs,
                    'category_id' => $a['cate'],
                    'sheet_id' => $a['id'],
                    'created_on' => date('Y-m-d H:i:s')
                        )
                );
                $i += 1;
            }
            if ($i !== 0) {
                echo json_encode(array($wpdb->insert_id));
            } else {
                echo "0";
            }

        endforeach;
    }
    die;
}
//get my library
if ($task == 'get_my_library') {
    $search = $_REQUEST['data'];
    $user = get_current_user_id();
    $sort = $_REQUEST['sort'];
    $query = 'SELECT sheet.*,dicts.sheet_name,dicts.description, ass.default_name AS assignment, cate.id AS cate FROM ' . $wpdb->prefix . 'dict_my_library AS lib JOIN ' . $wpdb->prefix . 'dict_homework_assignments AS ass '
            . 'JOIN ' . $wpdb->prefix . 'dict_my_library_sheet as sheet ON lib.id=sheet.library_id JOIN ' . $wpdb->prefix . 'dict_sheets AS dicts ON ass.id = dicts.assignment_id AND dicts.id=sheet.sheet_id JOIN ' . $wpdb->prefix . 'dict_sheet_categories AS cate ON cate.id=sheet.category_id ';

    if ($search !== null) {
        $query .= 'WHERE  lib.created_by=' . $user . ' AND dicts.sheet_name LIKE "%' . $search . '%" ORDER BY sheet.created_on DESC';
    } else if ($sort !== null) {
        if ($sort == "upward") {
            $query .= 'WHERE  lib.created_by=' . $user . ' ORDER BY dicts.sheet_name ASC';
        } else if ($sort == "downward") {
            $query .= 'WHERE  lib.created_by=' . $user . ' ORDER BY dicts.sheet_name DESC';
        }
    } else {
        $query .= 'WHERE  lib.created_by=' . $user . ' ORDER BY sheet.created_on DESC';
    }

    $result = $wpdb->get_results($query);
    //var_dump($query);
    if ($result) {
        foreach ($result as $value) {

            $arr_lib[] = array(
                'id' => $value->id,
                'library_id' => $value->library_id,
                'name' => $value->sheet_name,
                'assignment' => $value->assignment,
                'cate' => $value->cate,
                'description' => $value->description,
                'sheet_id' => $value->sheet_id
            );
        }
        echo json_encode(array('library' => $arr_lib));
    } else {
        echo 0;
    }
    die;
}
if ($task == 'delete_my_library') {
    $id = $_REQUEST["data"];
    $query1 = $wpdb->get_var("SELECT id FROM " . $wpdb->prefix . "dict_my_library_sheet WHERE id=$id");
    if ($query1 !== null) {
        $query = "DELETE FROM " . $wpdb->prefix . "dict_my_library_sheet WHERE id=$id";
        $result = $wpdb->query($query);
        echo "1";
    }

    die;
}
if ($task == 'update_desc_worksheet') {
    $id = $_REQUEST["sid"];
    $desc = $_REQUEST["desc"];
//    var_dump($desc);die;
    if ($id !== null) {
        $query = "UPDATE " . $wpdb->prefix . "dict_sheets SET description='$desc' WHERE id=$id";
        $result = $wpdb->query($query);
        if ($result) {
            echo 1;
        }
    }
    die;
}
if ($task == 'get_cate') {
    $query = 'SELECT * FROM ' . $wpdb->prefix . 'dict_sheet_categories';
    $result = $wpdb->get_results($query);
    if ($result) {
        foreach ($result as $v) {
            $arr[] = array(
                'id' => $v->id,
                'name' => $v->name
            );
        }
        echo json_encode(array("cate" => $arr));
    }
    die;
}
if ($task == 'add_my_lesson') {
    $id = $_REQUEST["lid"];
    $name = $_REQUEST["name"];
    $desc = $_REQUEST["desc"];
    $cate = $_REQUEST["cate"];
    $user = get_current_user_id();
    $arr = $_REQUEST["sheet"];
    $a = array();

    if ($id == null) {

        $result = $wpdb->insert(
                $wpdb->prefix . 'dict_my_lesson', array(
            'name' => $name,
            'description' => $desc,
            'category_id' => $cate,
            'created_by' => $user,
            'created_on' => date('Y-m-d H:i:s')
                )
        );

        $rs = $wpdb->insert_id;
    } else {
        $rs = (int) $id;
    }
    if ($rs !== null) {
        foreach ($arr as $k => $v):
            $a["id"] = $v;
            $query2 = "SELECT sheet_id FROM " . $wpdb->prefix . "dict_my_lesson_sheet WHERE sheet_id=" . $a['id'] . " AND lesson_id=$rs";
            $exst = $wpdb->get_var($query2);

            if ($exst == null) {
                $countls = "SELECT COUNT(lesson_id) FROM " . $wpdb->prefix . "dict_my_lesson_sheet WHERE lesson_id=$rs";
                $resultls = $wpdb->get_var($countls);

                $result2 = $wpdb->insert(
                        $wpdb->prefix . 'dict_my_lesson_sheet', array(
                    'lesson_id' => $rs,
                    'sheet_id' => $a["id"],
                    'order' => $resultls + 1,
                    'created_on' => date('Y-m-d H:i:s')
                        )
                );
            }
        endforeach;
        if ($result2) {
            echo json_encode(array($wpdb->insert_id));
        } else {
            echo "0";
        }
    }
    die;
}
if ($task == 'get_my_lesson') {
    $user = get_current_user_id();
    $search = $_REQUEST["search"];
    $sort = $_REQUEST['sort'];
    $query = 'SELECT ls.*, cate.id AS cate FROM ' . $wpdb->prefix . 'dict_my_lesson AS ls '
            . ' JOIN ' . $wpdb->prefix . 'dict_sheet_categories AS cate '
            . 'ON  cate.id=ls.category_id ';
    if ($search !== NULL) {
        $query .= 'WHERE  ls.created_by=' . $user . ' AND ls.name LIKE "%' . $search . '%" ORDER BY ls.created_on DESC';
    } else if ($sort !== null) {
        if ($sort == "upward") {
            $query .= 'WHERE  ls.created_by=' . $user . ' ORDER BY ls.name ASC';
        } else if ($sort == "downward") {
            $query .= 'WHERE  ls.created_by=' . $user . ' ORDER BY ls.name DESC';
        }
    } else {
        $query .= 'WHERE  ls.created_by=' . $user . ' ORDER BY ls.created_on DESC';
    }
    $result = $wpdb->get_results($query);
    if ($result) {
        foreach ($result as $value) {
            $arr_lib[] = array(
                'id' => $value->id,
                'name' => $value->name,
                'cate' => $value->cate,
                'description' => $value->description,
            );
        }

        echo json_encode(array('lesson' => $arr_lib));
    } else {
        echo 0;
    }
    die;
}
//get worksheets for lesson
if ($task == 'get_my_lesson_sheet') {
    $id = $_REQUEST["data"];
    $query = "SELECT sheet.*,ds.sheet_name,ds.description FROM " . $wpdb->prefix . "dict_my_lesson_sheet AS sheet JOIN " . $wpdb->prefix . "dict_sheets AS ds WHERE sheet.sheet_id=ds.id AND lesson_id=$id ORDER BY sheet.order DESC";
    $result = $wpdb->get_results($query);

    if ($result) {
        foreach ($result as $value) {
            $arr_lib[] = array(
                'id' => $value->id,
                'sheet_name' => $value->sheet_name,
                'description' => $value->description,
                'sheet_id' => $value->sheet_id,
                'lesson_id' => $value->lesson_id
            );
        }
        echo json_encode(array('sheet' => $arr_lib));
    } else {
        echo 0;
    }
}
//delete lesson
if ($task == 'delete_my_lesson') {
    $id = $_REQUEST["data"];
    $slid = $_REQUEST["slid"];
    if ($id !== null) {
        $query1 = $wpdb->get_var("SELECT id FROM " . $wpdb->prefix . "dict_my_lesson WHERE id=$id");
        if ($query1 !== null) {
            $query = "DELETE FROM " . $wpdb->prefix . "dict_my_lesson WHERE id=$id";
            $query2 = "DELETE FROM " . $wpdb->prefix . "dict_my_lesson_sheet WHERE lesson_id=$id";
            $result = $wpdb->query($query);
            $result2 = $wpdb->query($query2);
            if ($result && $result2) {
                echo "1";
            }
        }
    }
    if ($slid !== null) {
        $lesson_id = $wpdb->get_var("SELECT lesson_id FROM " . $wpdb->prefix . "dict_my_lesson_sheet WHERE id=$slid");

        $query2 = "DELETE FROM " . $wpdb->prefix . "dict_my_lesson_sheet WHERE id=$slid";
        $result = $wpdb->query($query2);
        if ($result) {
            $qr = "SELECT * FROM " . $wpdb->prefix . "dict_my_lesson_sheet AS sheet WHERE lesson_id=$lesson_id ORDER BY sheet.order ASC";
            $rs = $wpdb->get_results($qr);
            $i = 1;
            foreach ($rs as $value):
                $query = $wpdb->query("UPDATE " . $wpdb->prefix . "dict_my_lesson_sheet AS sheet SET sheet.order=$i WHERE id=" . $value->id);
                $i++;
            endforeach;

            echo "1";
        }
    }
    die;
}
//get description for worksheet
if ($task == 'get_desc_worksheet') {
    $id = $_REQUEST["data"];
    $query = "SELECT ds.id,ds.sheet_name,ds.description FROM " . $wpdb->prefix . "dict_sheets AS ds WHERE ds.id=$id";
    $result = $wpdb->get_results($query);
//    var_dump($query);die;
    if ($result) {
        foreach ($result as $value) {
            $arr_lib[] = array(
                'id' => $value->id,
                'sheet_name' => $value->sheet_name,
                'description' => $value->description,
            );
        }
        echo json_encode(array('sheet' => $arr_lib));
    } else {
        echo 0;
    }
    die;
}
if ($task == 'update_desc_lesson') {
    $id = $_REQUEST["lid"];
    $desc = $_REQUEST["desc"];
//var_dump($id);die;
    if ($id !== null) {
        $query = "UPDATE " . $wpdb->prefix . "dict_my_lesson SET description='$desc' WHERE id=$id";
        $result = $wpdb->query($query);
        if ($result) {
            $qr_desc = "SELECT * FROM " . $wpdb->prefix . "dict_my_lesson WHERE id=$id";
            $desc = $wpdb->get_results($qr_desc);
            foreach ($desc as $v) {
                $arr[] = array(
                    'id' => $v->id,
                    'desc' => $v->description
                );
            }
            echo json_encode($arr);
        }
    }
    die;
}
//update order for worksheet
if ($task == 'update_order_sheet') {
    $order = $_REQUEST['order'];
    $id = $_REQUEST['id'];
    $lid = $_REQUEST['lid'];
    $select = "SELECT sheet.order FROM " . $wpdb->prefix . "dict_my_lesson_sheet AS sheet  WHERE id=$id";
    $resultsl = (int) $wpdb->get_var($select);
    //var_dump($resultsl);die;
    if ($order == "up") {
        $index = $resultsl + 1;
        $index2 = $resultsl - 1;
        $update2 = $wpdb->query("UPDATE " . $wpdb->prefix . "dict_my_lesson_sheet AS sheet SET sheet.order=" . $resultsl . " WHERE sheet.order=" . $index . " AND lesson_id=$lid");
        $update1 = $wpdb->query("UPDATE " . $wpdb->prefix . "dict_my_lesson_sheet AS sheet SET sheet.order=" . $index . " WHERE id=$id");
        if ($update1 && $update2) {
            echo 1;
        } else {
            echo 0;
        }
    } else if ($order == "down") {
        $index = $resultsl - 1;
        $index2 = $resultsl + 1;
        $update2 = $wpdb->query("UPDATE " . $wpdb->prefix . "dict_my_lesson_sheet AS sheet SET sheet.order=" . $resultsl . " WHERE sheet.order=" . $index . " AND lesson_id=$lid");
        $update1 = $wpdb->query("UPDATE " . $wpdb->prefix . "dict_my_lesson_sheet AS sheet SET sheet.order=" . $index . " WHERE id=$id");
        if ($update1 && $update2) {
            echo 1;
        } else {
            echo 0;
        }
    }
}
//get Ready-made Lesson
if ($task == 'get_groups_home_eng') {
    $user = get_current_user_id();
    $search = $_REQUEST["search"];
    $sort = $_REQUEST["sort"];
    $query = "SELECT g.id, g.name, g.password, g.created_on, g.created_by AS uid, gt.name AS group_type, display_name, g.active, slug, price,class_type_id,content,detail,ct.name AS class_name,gc.ordering,sh.sheet_name,sc.name AS cate FROM " . $wpdb->prefix . "dict_groups AS g 
LEFT JOIN " . $wpdb->prefix . "dict_group_types AS gt ON gt.id = g.group_type_id 
LEFT JOIN " . $wpdb->prefix . "dict_homeworks AS h ON h.group_id = g.id 
LEFT JOIN " . $wpdb->prefix . "users AS u ON u.ID = g.created_by 
LEFT JOIN " . $wpdb->prefix . "dict_group_details AS gc ON gc.group_id = g.id 
LEFT JOIN " . $wpdb->prefix . "dict_sheets AS sh ON sh.id = h.sheet_id
LEFT JOIN " . $wpdb->prefix . "dict_sheet_categories AS sc ON sc.id =sh.category_id
LEFT JOIN " . $wpdb->prefix . "dict_group_class_types AS ct ON ct.id = gc.class_type_id WHERE g.group_type_id = 2 AND slug = 'en-en'";
    //var_dump($query);die;
    if ($search !== NULL) {
        $query .= ' AND g.name LIKE "%' . $search . '%" GROUP BY g.id HAVING slug = "en-en" ORDER BY gc.ordering ASC';
    } else if ($sort !== null) {
        if ($sort == "upward") {
            $query .= ' GROUP BY g.id HAVING slug = "en-en" ORDER BY g.name ASC';
        } else if ($sort == "downward") {
            $query .= ' GROUP BY g.id HAVING slug = "en-en" ORDER BY g.name DESC';
        }
    }
    else{
        $query .= ' GROUP BY g.id HAVING slug = "en-en" ORDER BY gc.ordering ASC';
    }
    $groups = $wpdb->get_results($query);

    foreach ($groups as $v):
        $arr[] = array(
            'id' => $v->id,
            'name' => $v->name,
            'cate' => $v->cate,
            'sheet' => $v->sheet_name,
            'price' => $v->price,
            'content' => $v->content
        );
    endforeach;
    if($groups != NULL){
        echo json_encode(array('arr' => $arr));
    }
    else {
        echo "0";
    }
    die;
}
if ($task == 'get_groups_home_math') {
    $user = get_current_user_id();
    $search = $_REQUEST["search"];
    $sort = $_REQUEST["sort"];
    $query = "SELECT g.id, g.name, g.password, g.created_on, g.created_by AS uid, gt.name AS group_type, display_name, g.active, slug, price,class_type_id,content,detail,ct.name AS class_name,gc.ordering,sh.sheet_name,sc.name AS cate FROM " . $wpdb->prefix . "dict_groups AS g 
LEFT JOIN " . $wpdb->prefix . "dict_group_types AS gt ON gt.id = g.group_type_id 
LEFT JOIN " . $wpdb->prefix . "dict_homeworks AS h ON h.group_id = g.id 
LEFT JOIN " . $wpdb->prefix . "users AS u ON u.ID = g.created_by 
LEFT JOIN " . $wpdb->prefix . "dict_group_details AS gc ON gc.group_id = g.id 
LEFT JOIN " . $wpdb->prefix . "dict_sheets AS sh ON sh.id = h.sheet_id
LEFT JOIN " . $wpdb->prefix . "dict_sheet_categories AS sc ON sc.id =sh.category_id
LEFT JOIN " . $wpdb->prefix . "dict_group_class_types AS ct ON ct.id = gc.class_type_id WHERE g.group_type_id = 2 AND slug = 'en-math'";
    //var_dump($query);die;
    if ($search !== NULL) {
        $query .= ' AND g.name LIKE "%' . $search . '%" GROUP BY g.id HAVING slug = "en-math" ORDER BY gc.ordering ASC';
    } else if ($sort !== null) {
        if ($sort == "upward") {
            $query .= ' GROUP BY g.id HAVING slug = "en-math" ORDER BY g.name ASC';
        } else if ($sort == "downward") {
            $query .= ' GROUP BY g.id HAVING slug = "en-math" ORDER BY g.name DESC';
        }
    }
    else{
        $query .= ' GROUP BY g.id HAVING slug = "en-math" ORDER BY gc.ordering ASC';
    }
    $groups = $wpdb->get_results($query);

    foreach ($groups as $v):
        $arr[] = array(
            'id' => $v->id,
            'name' => $v->name,
            'cate' => $v->cate,
            'sheet' => $v->sheet_name,
            'price' => $v->price,
            'content' => $v->content
        );
    endforeach;

    if($groups != NULL){
        echo json_encode(array('arr' => $arr));
    }
    else {
        echo "0";
    }
    die;
}
//get workshet for Ready-made Lesson
if ($task == 'get_ready_lesson_sheet') {
    $id = $_REQUEST['data'];

    $query = "SELECT ds.* FROM " . $wpdb->prefix . "dict_sheets AS ds JOIN " . $wpdb->prefix . "dict_homeworks AS dh ON ds.id=dh.sheet_id WHERE group_id=$id";
    
    $result = $wpdb->get_results($query);
    
    if (!empty($result)) {
        foreach ($result as $value) {
            $arr[] = array(
                'id' => $value->id,
                'sheet_name' => $value->sheet_name,
                'description' => $value->description
            );
        }
        echo json_encode(array("sheet" => $arr));
    }
}
//get Lesson and Subject of Mathematics
if ($task == 'get_level') {
    $cate = $_REQUEST['cate'];
    $level2 = $_REQUEST['level'];
    $main_categories = MWDB::get_grades(array('type' => 'MATH', 'level' => 0, 'admin_only' => 1, 'orderby' => 'ordering', 'order-dir' => 'asc', 'cate' => $cate));
    $levels = MWDB::get_grades(array('type' => 'MATH', 'level' => 1, 'orderby' => 'ordering', 'order-dir' => 'asc'));
    $html = '';
    if ($cate !== NULL) {

        $html .= ' <option selected value="">-Subject-</option>';
        foreach ($main_categories as $item) {
            foreach ($levels as $level) {
                if ($level->parent_id == $item->id) {
                    $html .= '<option value="' . $level->id . '"> ' . $level->name . '  </option>';
                }
            }
        }
    }
    $html2 = '';
    $levels2 = MWDB::get_grades(array('type' => 'MATH', 'level' => 1, 'orderby' => 'ordering', 'order-dir' => 'asc', 'cate' => $level2));
    $sublevels = MWDB::get_grades(array('type' => 'MATH', 'level' => 2, 'orderby' => 'ordering', 'order-dir' => 'asc'));
    $html2 .= ' <option selected value="">-Lesson-</option>';
    foreach ($levels2 as $level) {

        foreach ($sublevels as $sublevel) {
            if ($sublevel->parent_id == $level->id) {
                $html2 .= '<option value="' . $sublevel->id . '">' . $sublevel->name . '</option>';
            }
        }
    }

    echo json_encode(array("level" => $html, "sublevel" => $html2));
    die;
}
//add my subject
if ($task == 'add_my_subject') {
    $id = $_REQUEST["subid"];
    $name = $_REQUEST["name"];
    $desc = $_REQUEST["desc"];
    $cate = $_REQUEST["cate"];
    $user = get_current_user_id();
    $arr = $_REQUEST["lesson"];
    $a = array();

    if ($id == null) {

        $result = $wpdb->insert(
                $wpdb->prefix . 'dict_my_subject', array(
            'name' => $name,
            'description' => $desc,
            'category_id' => $cate,
            'created_by' => $user,
            'created_on' => date('Y-m-d H:i:s')
                )
        );

        $rs = $wpdb->insert_id;
    } else {
        $rs = (int) $id;
    }
    if ($rs !== null) {
        foreach ($arr as $k => $v):
            $a["id"] = $v;
            $query2 = "SELECT lesson_id FROM " . $wpdb->prefix . "dict_my_subject_lesson WHERE lesson_id=" . $a['id'] . " AND subject_id=$rs";
            $exst = $wpdb->get_var($query2);

            if ($exst == null) {
                $countls = "SELECT COUNT(subject_id) FROM " . $wpdb->prefix . "dict_my_subject_lesson WHERE subject_id=$rs";
                $resultls = $wpdb->get_var($countls);

                $result2 = $wpdb->insert(
                        $wpdb->prefix . 'dict_my_subject_lesson', array(
                    'subject_id' => $rs,
                    'lesson_id' => $a["id"],
                    'ordering' => $resultls + 1,
                    'created_on' => date('Y-m-d H:i:s')
                        )
                );
            }
        endforeach;
        if ($result2) {
            echo json_encode(array($wpdb->insert_id));
        } else {
            echo "0";
        }
    }
    die;
}
//get my subject
if ($task == 'get_my_subject') {
    $user = get_current_user_id();
    $search = $_REQUEST["search"];
    $sort = $_REQUEST['sort'];
    $query = 'SELECT ls.*, cate.id AS cate FROM ' . $wpdb->prefix . 'dict_my_subject AS ls '
            . ' JOIN ' . $wpdb->prefix . 'dict_sheet_categories AS cate '
            . 'ON  cate.id=ls.category_id ';
    if ($search !== NULL) {
        $query .= 'WHERE  ls.created_by=' . $user . ' AND ls.name LIKE "%' . $search . '%" ORDER BY ls.created_on DESC';
    } else if ($sort !== null) {
        if ($sort == "upward") {
            $query .= 'WHERE  ls.created_by=' . $user . ' ORDER BY ls.name ASC';
        } else if ($sort == "downward") {
            $query .= 'WHERE  ls.created_by=' . $user . ' ORDER BY ls.name DESC';
        }
    } else {
        $query .= 'WHERE  ls.created_by=' . $user . ' ORDER BY ls.created_on DESC';
    }
    $result = $wpdb->get_results($query);
    //var_dump($query);die;
    // var_dump($result);die;

    if ($result) {
        foreach ($result as $value) {
            $arr_lib[] = array(
                'id' => $value->id,
                'name' => $value->name,
                'cate' => $value->cate,
                'description' => $value->description,
            );
        }

        echo json_encode(array('subject' => $arr_lib));
    } else {
        echo 0;
    }
    die;
}
//get description for worksheet
if ($task == 'get_desc_subject') {
    $id = $_REQUEST["data"];
    $query = "SELECT ds.id,ds.description FROM " . $wpdb->prefix . "dict_my_subject AS ds WHERE ds.id=$id";
    $result = $wpdb->get_results($query);
//    var_dump($query);die;
    if ($result) {
        foreach ($result as $value) {
            $arr_lib[] = array(
                'id' => $value->id,
                'description' => $value->description,
            );
        }
        echo json_encode(array('sheet' => $arr_lib));
    } else {
        echo 0;
    }
    die;
}
if ($task == 'update_desc_subject') {
    $id = $_REQUEST["sid"];
    $desc = $_REQUEST["desc"];
//    var_dump($desc);die;
    if ($id !== null) {
        $query = "UPDATE " . $wpdb->prefix . "dict_my_subject SET description='$desc' WHERE id=$id";
        $result = $wpdb->query($query);
        if ($result) {
            echo 1;
        }
    }
    die;
}
//get list lesson's subject
if ($task == 'get_subject_lesson') {
    $id = $_REQUEST['subid'];
    $query = "SELECT sl.*, ls.name as lesson , ls.description as lesson_desc FROM " . $wpdb->prefix . "dict_my_subject_lesson as sl JOIN " . $wpdb->prefix . "dict_my_lesson AS ls ON sl.lesson_id=ls.id WHERE subject_id=$id ORDER BY ordering DESC";
    $lesson = $wpdb->get_results($query);
    if ($lesson !== NULL) {
        foreach ($lesson as $v) {
            $arr[] = array(
                'id' => $v->id,
                'lesson' => $v->lesson,
                'subject_id' => $v->subject_id,
                'lesson_id' => $v->lesson_id,
                'lesson_desc' => $v->lesson_desc
            );
        }
        echo json_encode($arr);
    }
    die;
}
if ($task == 'delete_lesson_subject') {
    $sublid = $_REQUEST["sublid"];
    $subid = $_REQUEST["subid"];
    if ($subid !== null) {
        $query1 = $wpdb->get_var("SELECT id FROM " . $wpdb->prefix . "dict_my_subject WHERE id=$subid");
        if ($query1 !== null) {
            $result2 = $wpdb->delete(
                    $wpdb->prefix . 'dict_my_subject', array(
                'id' => $subid
                    )
            );
            $result3 = $wpdb->delete(
                    $wpdb->prefix . 'dict_my_subject_lesson', array(
                'subject_id' => $subid
                    )
            );

            if ($result2 && $result3) {
                echo "1";
            }
        }
    }
    if ($sublid !== null) {
        $subject_id = $wpdb->get_var("SELECT subject_id FROM " . $wpdb->prefix . "dict_my_subject_lesson WHERE id=$sublid");

        $result = $wpdb->delete(
                $wpdb->prefix . 'dict_my_subject_lesson', array(
            'id' => $sublid
                )
        );
        if ($result) {
            $qr = "SELECT * FROM " . $wpdb->prefix . "dict_my_subject_lesson AS sheet WHERE subject_id=$subject_id ORDER BY sheet.ordering ASC";
            $rs = $wpdb->get_results($qr);
            $i = 1;
            foreach ($rs as $value):
                $query = $wpdb->query("UPDATE " . $wpdb->prefix . "dict_my_subject_lesson AS sheet SET sheet.ordering=$i WHERE id=" . $value->id);
                $i++;
            endforeach;

            echo "1";
        }
    }
    die;
}
//update order for lesson
if ($task == 'update_order_lesson') {

    $ordering = $_REQUEST['ordering'];

    $sublid = $_REQUEST['sublid'];
    $subid = $_REQUEST['subid'];
    $select = "SELECT sheet.ordering FROM " . $wpdb->prefix . "dict_my_subject_lesson AS sheet  WHERE id=$sublid";
    $resultsl = (int) $wpdb->get_var($select);
    //var_dump($resultsl);die;
    if ($ordering == "up") {
        $index = $resultsl + 1;
        $index2 = $resultsl - 1;
        $update2 = $wpdb->query("UPDATE " . $wpdb->prefix . "dict_my_subject_lesson AS sheet SET sheet.ordering=" . $resultsl . " WHERE sheet.ordering=" . $index . " AND subject_id=$subid");
        $update1 = $wpdb->query("UPDATE " . $wpdb->prefix . "dict_my_subject_lesson AS sheet SET sheet.ordering=" . $index . " WHERE id=$sublid");
        //var_dump(" UPDATE " . $wpdb->prefix . "dict_my_subject_lesson AS sheet SET sheet.ordering=" . $resultsl . " WHERE sheet.ordering=" . $index . " AND subject_id=$subid");
        //var_dump("UPDATE " . $wpdb->prefix . "dict_my_subject_lesson AS sheet SET sheet.ordering=" . $index . " WHERE id=$sublid");die;
        if ($update1 && $update2) {
            echo 1;
        } else {
            echo 0;
        }
    } else if ($ordering == "down") {
        $index = $resultsl - 1;
        $index2 = $resultsl + 1;
        $update2 = $wpdb->query("UPDATE " . $wpdb->prefix . "dict_my_subject_lesson AS sheet SET sheet.ordering=" . $resultsl . " WHERE sheet.ordering=" . $index . " AND subject_id=$subid");
        $update1 = $wpdb->query("UPDATE " . $wpdb->prefix . "dict_my_subject_lesson AS sheet SET sheet.ordering=" . $index . " WHERE id=$sublid");
        //var_dump("UPDATE " . $wpdb->prefix . "dict_my_subject_lesson AS sheet SET sheet.ordering=" . $resultsl . " WHERE sheet.ordering=" . $index . " AND subject_id=$subid");
        //var_dump("UPDATE " . $wpdb->prefix . "dict_my_subject_lesson AS sheet SET sheet.ordering=" . $index . " WHERE id=$sublid");die;
        if ($update1 && $update2) {
            echo 1;
        } else {
            echo 0;
        }
    }
}
 //fucntion get dictionaries
if($task=='get_dictionaries'){
    $search=$_REQUEST['search'];
     $sort = $_REQUEST['sort'];
    $query='SELECT * FROM '.$wpdb->prefix.'dict_dictionaries';
    if($search!=''){
        $query.=" WHERE name LIKE '%$search%'";
    }else if ($sort !== null) {
        if ($sort == "upward") {
            $query .= ' ORDER BY name ASC';
        } else if ($sort == "downward") {
            $query .= ' ORDER BY name DESC';
        }
    }
    $result=$wpdb->get_results($query);
    if($result!=null){
    foreach ($result as $v){
        $arr[]=array(
        'id'=>$v->id,
            'name'=>$v->name,
        );
    }
    echo json_encode($arr);
    }else{
        echo 0;
    }
}
if($task=='ik_check_user_student'){
	global $wpdb;
	$sub_type = '';
        $user_id = get_current_user_id();
	$user =  get_user_by( 'id', $user_id );

	// check teacher role
	if(isset($user->roles) && is_array($user->roles)) {
		//check role
		if(in_array('mw_qualified_teacher', $user->roles)) {
			$teacher_type = 'T-Qual';
		}else if(in_array('mw_registered_teacher', $user->roles)) {
			$teacher_type = 'T-Reg';
		}else if(in_array('mw_registered_math_teacher', $user->roles)) {
			$teacher_type = 'T-M-Reg';
		}else if(in_array('mw_qualified_math_teacher', $user->roles)) {
			$teacher_type = 'T-M-Qual';
		}else {
			$created_group = $wpdb->get_col(
				'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'dict_groups WHERE created_by = ' . $user_id
			);

			if(!empty($created_group[0])) {
				$teacher_type = 'Teacher';
			}
		}
	}
	
		echo $teacher_type;
                die;
}
//add My Lesson from Public Lesson
if($task=='add_public_lesson'){
    $arr=$_REQUEST['data'];
    $user_id = get_current_user_id();
    foreach ($arr as $v){

    $result=$wpdb->insert($wpdb->prefix.'dict_my_lesson',array(
        'category_id'=>$v['cate'],
        'name'=>$v['name'],
        'description'=>$v['desc'],
        'created_by'=>$user_id,
        'created_on'=>date('Y-m-d H:i:s'),
        'group_id'=>$v['id']
        
    ));
    if($result){
        echo 1;
    }else{
        echo 0;
    }
    }
}