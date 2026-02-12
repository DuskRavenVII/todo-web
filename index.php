<?php
session_start();
include("db.php");
$login = 0;
$message = '';
$tasks = [];


if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

if (isset($_SESSION['user_id'])) {
    $login = 1;
    $user_id = $_SESSION['user_id'];
    $tasks = loadtasks($user_id);
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["title_add"])) {
        $title = htmlspecialchars(trim($_POST['title_add']));
        $description = htmlspecialchars(trim($_POST['description_add'] ?? ''));
        $due_date = $_POST['due_date_add'];
        $user_id = $_SESSION['user_id'];
        if (empty($title) || empty($due_date)) {
            $_SESSION['message'] = "Title and due date are required!";
        } else {
            addtasks($title, $description, $due_date, $user_id);
            header("Location: index.php");
            exit();
        }
    } elseif (isset($_POST["description_up"])) {
        $title = htmlspecialchars(trim($_POST['title_up']));
        $description = htmlspecialchars(trim($_POST['description_up'] ?? ''));
        $due_date = $_POST['due_date_up'];
        $task_id = $_POST['task_id'];
        $user_id = $_SESSION['user_id'];
        updatetasks($title, $description, $due_date, $task_id, $user_id);
        header("Location: index.php");
        exit();
    } elseif (isset($_POST["username_in"])) {
        $username = htmlspecialchars(trim($_POST['username_in']));
        $password = $_POST['password_in'];
        if (empty($username) || empty($password)) {
            $_SESSION['message'] = "All fields are required!";
        } else {
            SignIn($username, $password);
            header("Location: index.php");
            exit();
        }
    } elseif (isset($_POST["username_up"])) {
        $username = htmlspecialchars(trim($_POST['username_up']));
        $password = $_POST['password_up'];
        $passwordc = $_POST['password_up_c'];
        $email = $_POST['email_up'];
        if (empty($username) || empty($email) || empty($password)) {
            $_SESSION['message'] = "All fields are required!";
        } elseif ($password !== $passwordc) {
            $_SESSION['message'] = "Passwords do not match!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['message'] = "Invalid email format!";
        } else {
            SignUp($username, $password, $email);
            header("Location: index.php");
            exit();
        }
    } elseif (isset($_POST["delete_task"]) && isset($_POST["task_id"])) {
        $user_id = $_SESSION['user_id'];
        deletetasks($_POST["task_id"], $user_id);
        header("Location: index.php");
        exit();
    } elseif (isset($_POST["logout"])) {
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit();
    }
}
function SignIn($user, $password)
{
    global $conn, $login, $message;

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $user);
        $stmt->execute();

        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();

        if ($userData && password_verify($password, $userData['password'])) {
            $_SESSION['user_id'] = $userData['id'];
            $_SESSION['username'] = $userData['username'];
            $login = 1;
            $_SESSION['message'] = "Login successful! Welcome " . $userData['username'];
        } else {
            $_SESSION['message'] = "Invalid username or password!";
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
    }
}

function SignUp($user, $password, $email)
{
    global $conn, $message;

    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $user, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['message'] = "Username or email already exists!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $user, $email, $hashed_password);
        $stmt->execute();
        $_SESSION['message'] = "Account created successfully! Please sign in.";
    }
}

function addtasks($title, $description, $due_date, $user_id)
{
    global $conn, $tasks;
    $stmt = $conn->prepare("INSERT INTO tasks (title, description, due_date, user_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $title, $description, $due_date, $user_id);
    $stmt->execute();
    $tasks = loadtasks($user_id);
    $_SESSION['message'] = "Task added successfully!";
}
function updatetasks($title, $description, $due_date, $task_id, $user_id)
{
    global $conn, $message,  $tasks;

    if ($task_id) {
        try {
            $stmt = $conn->prepare("UPDATE tasks SET title = ?, description = ?, due_date = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sssii", $title, $description, $due_date, $task_id, $user_id);
            $stmt->execute();

            $_SESSION['message'] = "Task updated successfully!";
            $tasks = loadtasks($user_id);
        } catch (Exception $e) {
            $_SESSION['message'] = "Error updating task: " . $e->getMessage();
        }
    }
}

function loadtasks($user_id)
{
    global $conn;
    $tasks = [];

    $stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY due_date ASC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }

    return $tasks;
}

function deletetasks($task_id, $user_id)
{
    global $conn, $tasks;
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $_SESSION['message'] = "Task deleted successfully!";
    } else {
        $_SESSION['message'] = "Error: Task not found or you don't have permission!";
    }

    $tasks = loadtasks($user_id);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo Web</title>

    <link rel="stylesheet" href="static/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="static/css/style.css">
    <style></style>
</head>

<body>
    <div class="d-flex">
        <nav class="nav flex-column nav-pills bg-primary p-3 vh-100 shadow-sm rounded-end" id="pillNav2" role="tablist" style="min-width: 200px;">
            <a href="#" class="nav-link text-white" id="nav-home" data-form="home">Home</a>
            <?php if (isset($login) && $login == 1): ?>
                <a href="#" class="nav-link text-white" id="nav-add" data-form="add">Add Tasks</a>
            <?php endif; ?>
            <?php if (isset($login) && $login == 1): ?>
                <a href="#" class="nav-link text-white" id="nav-exit" data-form="exit">Exit</a>
            <?php else: ?>
                <a href="#" class="nav-link text-white" id="nav-login" data-form="auth">Sign In / Sign Up</a>
            <?php endif; ?>
        </nav>

        <div class="flex-grow-1 p-4">
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo (strpos($message, 'Error') !== false || strpos($message, 'Invalid') !== false || strpos($message, 'required') !== false) ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <div id="home-content" class="form-container active" style="max-width: 90%;">
                <h3>Welcome to Todo</h3>
                <p>To get started, log in to your account.</p>
                <?php if (isset($login) && $login == 1): ?>
                    <?php if (!empty($tasks)): ?>
                        <div class="mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Tasks (<?= count($tasks) ?>)</h5>
                                <button class="btn btn-sm btn-primary" onclick="showAddForm()">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z" />
                                    </svg>
                                    Add Task
                                </button>
                            </div>

                            <div class="row g-3">
                                <?php foreach ($tasks as $task):
                                    $dueDate = strtotime($task['due_date']);
                                    $today = strtotime('today');
                                    $diff = ($dueDate - $today) / (60 * 60 * 24);
                                    $statusClass = $diff < 0 ? 'border-danger' : ($diff == 0 ? 'border-warning' : ($diff <= 2 ? 'border-info' : 'border-success'));
                                ?>
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <div class="card <?= $statusClass ?> border-start border-3 shadow-sm task-card">
                                            <div class="card-body py-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <h6 class="card-title mb-0 me-2"><?= htmlspecialchars($task['title']) ?></h6>
                                                            <?php if ($diff < 0): ?>
                                                                <span class="badge bg-danger badge-sm">Overdue</span>
                                                            <?php elseif ($diff == 0): ?>
                                                                <span class="badge bg-warning badge-sm">Today</span>
                                                            <?php elseif ($diff <= 2): ?>
                                                                <span class="badge bg-info badge-sm">Soon</span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <?php if (!empty($task['description'])): ?>
                                                            <p class="card-text text-muted small mb-1">
                                                                <?= htmlspecialchars(mb_strimwidth($task['description'], 0, 100, '...')) ?>
                                                            </p>
                                                        <?php endif; ?>

                                                        <small class="text-muted">
                                                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                                                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z" />
                                                            </svg>
                                                            <?= htmlspecialchars(date('d/m/Y', strtotime($task['due_date']))) ?>
                                                        </small>
                                                    </div>

                                                    <div class="btn-group btn-group-sm ms-3">
                                                        <button type="button" class="btn btn-outline-primary edit-task-btn"
                                                            data-id="<?= $task['id'] ?>"
                                                            data-title="<?= htmlspecialchars($task['title']) ?>"
                                                            data-desc="<?= htmlspecialchars($task['description'] ?? '') ?>"
                                                            data-date="<?= htmlspecialchars($task['due_date']) ?>">
                                                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                                <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z" />
                                                            </svg>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger delete-task-btn"
                                                            data-id="<?= $task['id'] ?>">
                                                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z" />
                                                                <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <svg width="48" height="48" fill="#dee2e6" viewBox="0 0 16 16" class="mb-3">
                                <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z" />
                                <path d="M3 8.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5z" />
                            </svg>
                            <h6 class="text-muted">No tasks yet</h6>
                            <p class="text-muted small">Create your first task to get started</p>
                            <button class="btn btn-primary btn-sm" onclick="showAddForm()">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z" />
                                </svg>
                                Add Task
                            </button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

            </div>
            <form class="form-container form-section" id="form-add" action="index.php" method="post">
                <h4 class="mb-3">Add New Task</h4>
                <div class="mb-3">
                    <label for="title_add" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title_add" name="title_add" required>
                </div>
                <div class="mb-3">
                    <label for="description_add" class="form-label">Description</label>
                    <textarea class="form-control" id="description_add" name="description_add" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label for="due_date_add" class="form-label">Due Date</label>
                    <input type="date" class="form-control" id="due_date_add" name="due_date_add" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Task</button>
            </form>

            <form class="form-container form-section" id="form-update" action="index.php" method="post">
                <h4 class="mb-3">Update Task</h4>
                <div class="mb-3">
                    <label for="title_up" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title_up" name="title_up" readonly>
                </div>
                <div class="mb-3">
                    <label for="description_up" class="form-label">Description</label>
                    <textarea class="form-control" id="description_up" name="description_up" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label for="due_date_up" class="form-label">Due Date</label>
                    <input type="date" class="form-control" id="due_date_up" name="due_date_up">
                </div>
                <button type="submit" class="btn btn-warning">Update Task</button>
            </form>

            <div class="form-container form-section" id="form-auth">
                <div class="auth-switch-buttons text-center mb-4">
                    <button type="button" class="btn btn-outline-primary" id="btn-show-signin">Sign In</button>
                    <button type="button" class="btn btn-outline-success" id="btn-show-signup">Sign Up</button>
                </div>

                <form action="index.php" method="post" id="form-signin">
                    <h4 class="mb-3">Sign In</h4>
                    <div class="mb-3">
                        <label for="username_in" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username_in" name="username_in" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_in" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password_in" name="password_in" required>
                    </div>
                    <input type="hidden" name="action" value="signin">
                    <button type="submit" class="btn btn-primary w-100">Sign In</button>
                </form>

                <form action="index.php" method="post" id="form-signup" style="display: none;">
                    <h4 class="mb-3">Create Account</h4>
                    <div class="mb-3">
                        <label for="username_up" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username_up" name="username_up" required>
                    </div>
                    <div class="mb-3">
                        <label for="email_up" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email_up" name="email_up" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_up" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password_up" name="password_up" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_up_c" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="password_up_c" name="password_up_c" required>
                    </div>
                    <input type="hidden" name="action" value="signup">
                    <button type="submit" class="btn btn-success w-100">Sign Up</button>
                </form>
            </div>
        </div>
    </div>

    <script src="static/js/script.js"></script>
    <script src="static/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>

</html>