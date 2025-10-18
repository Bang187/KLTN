<?php
include_once(__DIR__ . '/../model/modelmatchevent.php');
    class cMatchStats {
    public function screen(int $idMatch){
    $m = new mMatchEvent();

    if ($_SERVER['REQUEST_METHOD']==='POST') {
      if (isset($_POST['add_goal_home'])) {
        $m->addGoal(
          $idMatch,'home',
          (int)$_POST['home_member_id'],
          (int)$_POST['home_minute'],
          $_POST['home_goal_type'] ?? 'goal'
        );
        $this->redir('?page=match_stats&id_match='.$idMatch.'&ok=1');
      }
      if (isset($_POST['add_goal_away'])) {
        $m->addGoal(
          $idMatch,'away',
          (int)$_POST['away_member_id'],
          (int)$_POST['away_minute'],
          $_POST['away_goal_type'] ?? 'goal'
        );
        $this->redir('?page=match_stats&id_match='.$idMatch.'&ok=1');
      }
      if (isset($_POST['del_event'])) {
        $m->deleteEvent((int)$_POST['id_event'], $idMatch);
        $this->redir('?page=match_stats&id_match='.$idMatch.'&ok=1');
      }
      if (isset($_POST['finalize_match'])) {
        $m->FinalResultMatch($idMatch);
          $tournaId = isset($_GET['id']) ? (int)$_GET['id'] : (int)($m->getMatchBasic($idMatch)['id_tourna'] ?? 0);
          $this->redir('dashboard.php?page=schedule&id='.$tournaId.'&scoreok=1');
      }
    }

    $match = $m->getMatchBasic($idMatch);
    $homeMembers = $m->listMembersOfTeam((int)$match['home_team_id']);
    $awayMembers = $m->listMembersOfTeam((int)$match['away_team_id']);
    $events = $m->listEvents($idMatch);
    // đảm bảo score hiển thị theo event
    $m->UpdateMatchScore($idMatch);
    $match = $m->getMatchBasic($idMatch);

    include __DIR__ . '/../view/match_event.php';
  }

  private function redir($url){
    if (!headers_sent()) { header('Location: '.$url); exit; }
    echo '<script>location.href="'.htmlspecialchars($url,ENT_QUOTES).'";</script>'; exit;
  }
    }
?>