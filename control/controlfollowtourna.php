<?php
include_once(__DIR__ . '/../model/modelfollowtourna.php');

class cFollow {
    public function toggleFollow($id_user, $id_tourna) {
        $p = new mFollow();

        $exists = $p->checkFollowExists($id_user, $id_tourna);
        if ($exists) {
            // nếu đã follow thì chuyển is_active = 0 (bỏ theo dõi)
            return $p->unfollow($id_user, $id_tourna) ? 'unfollowed' : false;
        } else {
            // nếu chưa follow thì thêm mới
            return $p->follow($id_user, $id_tourna);
        }
    }
    public function isFollowing($id_user, $id_tourna) {
    $p = new mFollow();
    return $p->checkFollowExists($id_user, $id_tourna);
}
// Lấy danh sách giải đã theo dõi
public function getFollowedTournaments($id_user) {
    $p = new mFollow();
    return $p->getFollowedTournaments($id_user);
}
}