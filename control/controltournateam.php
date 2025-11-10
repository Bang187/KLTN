<?php
require_once __DIR__ . '/../model/modeltournateam.php';

class cTournaTeam {
    public function approve(int $ttId, int $adminId): bool {
        $m = new mtournateam();
        return $m->approveRegistration($ttId, $adminId, true);
    }
    public function reject(int $ttId, int $adminId): bool {
        $m = new mtournateam();
        return $m->approveRegistration($ttId, $adminId, false);
    }
}

?>