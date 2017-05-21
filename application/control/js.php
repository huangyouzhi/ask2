<?php

!defined('IN_ASK2') && exit('Access Denied');

class jscontrol extends base {

    function jscontrol(& $get, & $post) {
        $this->base($get,$post);
        $this->load('question');
        $this->load('datacall');
    }

    function onview() {
        $id = intval($this->get[2]);
        $datacall = $_ENV['datacall']->get($id);
        !$datacall && exit(" document.write('非法调用!') ");
        $expressionarr = unserialize($datacall['expression']);
        $jscache = $this->cache->read('js_' . $id, $expressionarr['cachelife']);
        if (!$jscache) {
            $tpl = stripslashes(base64_decode($expressionarr['tpl']));
            $cid = 0;
            $cfield = '';
            if ($expressionarr['category']) {
                $cids = explode(":", substr($expressionarr['category'], 0, -1));
                foreach ($cids as $c) {
                    $c && $cid = $c;
                }
            }
            $cid && $cfield = 'cid' . $this->category[$cid]['grade'];

            $questionlist = $_ENV['question']->list_by_cfield_cvalue_status($cfield, $cid, $expressionarr['status'], $expressionarr['start'], $expressionarr['limit']);
            $jscache = '';
            foreach ($questionlist as $question) {
                $replaces = array();
                foreach ($question as $qkey => $qval) {
                    $replaces["[$qkey]"] = $qval;
                }
                $replaces['[title]'] = cutstr(strip_tags($question['title']), $expressionarr['maxbyte']);
                $replaces['[qid]'] = $question['id'];
                $replaces['[time]'] = tdate($question['time']);
                $replaces['[category_name]'] = $this->category[$cid]['name'];
                $replaces['[cid]'] = $cid;
                $replaces['[author]'] = $question['author'];
                $replaces['[authorid]'] = $question['authorid'];
                $replaces['[answers]'] = $question['answers'];
                $replaces['[views]'] = $question['views'];
                $jscache.=$this->replacesitevar($tpl, $replaces);
            }
            $this->cache->write('js_' . $id, $jscache);
        }
       echo "document.write('$jscache')";
    }

    function replacesitevar($string, $replaces = array()) {
        return str_replace(array_keys($replaces), array_values($replaces), $string);
    }

}

?>