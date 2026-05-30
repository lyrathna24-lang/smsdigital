<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once 'database.php';

class RankingAPI {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Transfer monthly scores to ranking table
     */
    public function transferToRanking($data) {
        try {
            $monthName = $data['monthName'];
            $semester = $data['semester'];
            $students = $data['students'];
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // Clear existing rankings for this month and semester
            $deleteQuery = "DELETE FROM monthly_rankings WHERE month_name = :monthName AND semester = :semester";
            $deleteStmt = $this->db->prepare($deleteQuery);
            $deleteStmt->bindParam(':monthName', $monthName);
            $deleteStmt->bindParam(':semester', $semester);
            $deleteStmt->execute();
            
            // Insert new rankings
            $insertQuery = "INSERT INTO monthly_rankings (student_id, month_name, semester, total_score, average, rank, grade) 
                           VALUES (:studentId, :monthName, :semester, :totalScore, :average, :rank, :grade)";
            $insertStmt = $this->db->prepare($insertQuery);
            
            foreach ($students as $student) {
                $insertStmt->bindParam(':studentId', $student['studentId']);
                $insertStmt->bindParam(':monthName', $monthName);
                $insertStmt->bindParam(':semester', $semester);
                $insertStmt->bindParam(':totalScore', $student['totalScore']);
                $insertStmt->bindParam(':average', $student['average']);
                $insertStmt->bindParam(':rank', $student['rank']);
                $insertStmt->bindParam(':grade', $student['grade']);
                $insertStmt->execute();
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => "បានផ្ទេរទិន្នន័យចំណាត់ថ្នាក់ខែ $monthName ទៅតារាងចំណាត់ថ្នាក់ដោយជោគជ័យ"
            ];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'មានបញ្ហាក្នុងការផ្ទេរទិន្នន័យ: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Transfer top 5 students to honor roll
     */
    public function transferToHonorRoll($data) {
        try {
            $monthName = $data['monthName'];
            $semester = $data['semester'];
            $topStudents = $data['topStudents'];
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // Clear existing honor roll for this month and semester
            $deleteQuery = "DELETE FROM honor_rolls WHERE month_name = :monthName AND semester = :semester";
            $deleteStmt = $this->db->prepare($deleteQuery);
            $deleteStmt->bindParam(':monthName', $monthName);
            $deleteStmt->bindParam(':semester', $semester);
            $deleteStmt->execute();
            
            // Insert new honor roll students
            $insertQuery = "INSERT INTO honor_rolls (student_id, month_name, semester, total_score, average, rank, grade) 
                           VALUES (:studentId, :monthName, :semester, :totalScore, :average, :rank, :grade)";
            $insertStmt = $this->db->prepare($insertQuery);
            
            foreach ($topStudents as $student) {
                $insertStmt->bindParam(':studentId', $student['studentId']);
                $insertStmt->bindParam(':monthName', $monthName);
                $insertStmt->bindParam(':semester', $semester);
                $insertStmt->bindParam(':totalScore', $student['totalScore']);
                $insertStmt->bindParam(':average', $student['average']);
                $insertStmt->bindParam(':rank', $student['rank']);
                $insertStmt->bindParam(':grade', $student['grade']);
                $insertStmt->execute();
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => "បានផ្ទេរសិស្សចំណាត់ថ្នាក់លេខ 1-5 ខែ $monthName ទៅតារាងកិត្តិយសដោយជោគជ័យ"
            ];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'មានបញ្ហាក្នុងការផ្ទេរទិន្នន័យកិត្តិយស: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get monthly rankings
     */
    public function getMonthlyRankings($monthName, $semester) {
        try {
            $query = "SELECT mr.*, s.student_code, s.first_name, s.last_name, s.gender 
                     FROM monthly_rankings mr
                     JOIN students s ON mr.student_id = s.id
                     WHERE mr.month_name = :monthName AND mr.semester = :semester
                     ORDER BY mr.rank ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':monthName', $monthName);
            $stmt->bindParam(':semester', $semester);
            $stmt->execute();
            
            $rankings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $rankings
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'មានបញ្ហាក្នុងការទាញយកទិន្នន័យចំណាត់ថ្នាក់: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get honor roll students
     */
    public function getHonorRoll($monthName, $semester) {
        try {
            $query = "SELECT hr.*, s.student_code, s.first_name, s.last_name, s.gender 
                     FROM honor_rolls hr
                     JOIN students s ON hr.student_id = s.id
                     WHERE hr.month_name = :monthName AND hr.semester = :semester
                     ORDER BY hr.rank ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':monthName', $monthName);
            $stmt->bindParam(':semester', $semester);
            $stmt->execute();
            
            $honorRoll = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $honorRoll
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'មានបញ្ហាក្នុងការទាញយកទិន្នន័យកិត្តិយស: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all rankings for a semester
     */
    public function getSemesterRankings($semester) {
        try {
            $query = "SELECT mr.*, s.student_code, s.first_name, s.last_name, s.gender 
                     FROM monthly_rankings mr
                     JOIN students s ON mr.student_id = s.id
                     WHERE mr.semester = :semester
                     ORDER BY mr.month_name, mr.rank ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':semester', $semester);
            $stmt->execute();
            
            $rankings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $rankings
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'មានបញ្ហាក្នុងការទាញយកទិន្នន័យចំណាត់ថ្នាក់ឆមាស: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all honor rolls for a semester
     */
    public function getSemesterHonorRolls($semester) {
        try {
            $query = "SELECT hr.*, s.student_code, s.first_name, s.last_name, s.gender 
                     FROM honor_rolls hr
                     JOIN students s ON hr.student_id = s.id
                     WHERE hr.semester = :semester
                     ORDER BY hr.month_name, hr.rank ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':semester', $semester);
            $stmt->execute();
            
            $honorRolls = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $honorRolls
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'មានបញ្ហាក្នុងការទាញយកទិន្នន័យកិត្តិយសឆមាស: ' . $e->getMessage()
            ];
        }
    }
}

// Process API requests
try {
    $rankingAPI = new RankingAPI();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        // Handle GET requests
        $action = $_GET['action'] ?? '';
        $monthName = $_GET['monthName'] ?? '';
        $semester = $_GET['semester'] ?? '';
        
        switch ($action) {
            case 'getMonthlyRankings':
                $response = $rankingAPI->getMonthlyRankings($monthName, $semester);
                break;
            case 'getHonorRoll':
                $response = $rankingAPI->getHonorRoll($monthName, $semester);
                break;
            case 'getSemesterRankings':
                $response = $rankingAPI->getSemesterRankings($semester);
                break;
            case 'getSemesterHonorRolls':
                $response = $rankingAPI->getSemesterHonorRolls($semester);
                break;
            default:
                $response = ['success' => false, 'message' => 'សកម្មភាពមិនត្រឹមត្រូវ'];
        }
    } else {
        // Handle POST requests
        $action = $input['action'] ?? '';
        
        switch ($action) {
            case 'transferToRanking':
                $response = $rankingAPI->transferToRanking($input);
                break;
            case 'transferToHonorRoll':
                $response = $rankingAPI->transferToHonorRoll($input);
                break;
            default:
                $response = ['success' => false, 'message' => 'សកម្មភាពមិនត្រឹមត្រូវ'];
        }
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'មានបញ្ហា: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
