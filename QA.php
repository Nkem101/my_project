<?php
/**
 * 📝 Fun & Easy Tutorial: Dynamic Interview Questionnaire from Database
 * 
 * 🎯 Features:
 * ✅ Fetches interview questions dynamically from MySQL
 * ✅ Displays one question at a time
 * ✅ Stores user responses
 * ✅ Moves to the next question after answering
 * ✅ Tracks progress automatically
 * ✅ Textual outputs and flowchart explanations included 📜
 */

// 🔗 Step 1: Database Connection
$host = "igor.gold.ac.uk";
$user = "numez001"; //replace with username
$password = "xx";   // replace with Password
$database = "numez001"; replace with database name 
$connection = new mysqli($host, $user, $password, $database);
if ($connection->connect_error) {
    die("❌ Connection failed: " . $connection->connect_error);
}

/*
📌 Flowchart: How the connection works
-------------------------------------
[ Start ]
    ↓
[ Connect to MySQL ] → [ If connection fails ] → [ Display error & Exit ]
    ↓
[ Proceed to Table Setup ]
-------------------------------------
*/

// 📌 Step 2: Create Tables for Questions & Answers
$sql_questions = "CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_text TEXT NOT NULL
)";
$connection->query($sql_questions);

$sql_answers = "CREATE TABLE IF NOT EXISTS answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    user_answer TEXT,
    FOREIGN KEY (question_id) REFERENCES questions(id)
)";
$connection->query($sql_answers);

/*
📌 Flowchart: Table Setup
-------------------------------------
[ Check if 'questions' table exists ] → [ If not, create it ]
    ↓
[ Check if 'answers' table exists ] → [ If not, create it ]
-------------------------------------
*/

// 🧐 Step 3: Insert Sample Interview Questions (if table is empty)
$result = $connection->query("SELECT COUNT(*) as count FROM questions");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $connection->query("INSERT INTO questions (question_text) VALUES
        ('Tell me about yourself'),
        ('Why do you want to work here?'),
        ('What are your strengths?'),
        ('What are your career goals?'),
        ('What is your greatest weakness?')");
}

/*
📌 Flowchart: How Questions Are Inserted
-------------------------------------
[ Check if 'questions' table is empty ] → [ If empty, insert sample questions ]
-------------------------------------
*/

// 🔄 Step 4: Fetch the Next Unanswered Question
$question = "";
$question_id = null;
$result = $connection->query("SELECT q.id, q.question_text FROM questions q
                              LEFT JOIN answers a ON q.id = a.question_id
                              WHERE a.id IS NULL ORDER BY q.id ASC LIMIT 1");
if ($row = $result->fetch_assoc()) {
    $question = $row['question_text'];
    $question_id = $row['id'];
} else {
    echo "🎉 All questions have been answered!";
    exit;
}

// 🖥️ Display the Question in a Simple Form
echo "<form method='POST'>";
echo "<p><strong>🧐 Question:</strong> " . $question . "</p>";
echo "<input type='hidden' name='question_id' value='$question_id'>";
echo "<input type='text' name='answer' required placeholder='Type your answer here...'>";
echo "<button type='submit'>🚀 Submit Answer</button>";
echo "</form>";

/*
📌 Flowchart: How Questions Are Fetched
-------------------------------------
[ Select first unanswered question ] → [ Display it in a form ]
-------------------------------------
*/

// ✍️ Step 5: Handle User Responses
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $answer = $_POST['answer'];
    $question_id = $_POST['question_id'];
    $stmt = $connection->prepare("INSERT INTO answers (question_id, user_answer) VALUES (?, ?)");
    $stmt->bind_param("is", $question_id, $answer);
    $stmt->execute();
    $stmt->close();
    
    // 🔄 Refresh the page to show the next question
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/*
📌 Flowchart: Answer Submission
-------------------------------------
[ User submits answer ] → [ Store answer in database ] → [ Load next question ]
-------------------------------------
*/

// 🔚 Close Connection
$connection->close();