<?php

//DATABASE CONNECTION

$host = 'localhost';
$db   = 'it30_lab_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// SESSION
session_start();

// DETERMINE CURRENT SECTION
$section = $_GET['section'] ?? 'students';

//  CRUD OPERATIONS
$action = $_GET['action'] ?? '';


// STUDENTS CREATE
if ($section === 'students' && $action === 'create') {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $firstName = trim($_POST['student_first_name'] ?? '');
        $lastName  = trim($_POST['student_last_name'] ?? '');
        $course    = trim($_POST['student_course'] ?? '');

        if ($firstName !== '' && $lastName !== '' && $course !== '') {

            $sql = "
                INSERT INTO students (
                    student_first_name,
                    student_last_name,
                    student_course
                )
                VALUES (?, ?, ?)
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $firstName,
                $lastName,
                $course
            ]);

            header("Location: index.php?section=students");
            exit;
        }
    }
}

// STUDENTS UPDATE
if ($section === 'students' && $action === 'update') {

    $studentId = (int) ($_GET['id'] ?? 0);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $firstName = trim($_POST['student_first_name'] ?? '');
        $lastName  = trim($_POST['student_last_name'] ?? '');
        $course    = trim($_POST['student_course'] ?? '');

        $sql = "
            UPDATE students
            SET
                student_first_name = ?,
                student_last_name = ?,
                student_course = ?
            WHERE student_id = ?
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $firstName,
            $lastName,
            $course,
            $studentId
        ]);

        $_SESSION['alert'] = 'Student updated successfully.';

        header("Location: index.php?section=students");
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT *
        FROM students
        WHERE student_id = ?
    ");

    $stmt->execute([$studentId]);

    $student = $stmt->fetch();

    if (!$student) {
        die("Student not found.");
    }
}

// STUDENTS DELETE
if ($section === 'students' && $action === 'delete') {

    $studentId = (int) ($_GET['id'] ?? 0);

    try {

        $stmt = $pdo->prepare("
            DELETE FROM students
            WHERE student_id = ?
        ");

        $stmt->execute([$studentId]);

    } catch (PDOException $e) {

        echo "<p>Cannot delete this student because they have borrow records.</p>";
        echo "<p><a href='index.php?section=students'>Back</a></p>";
        exit;
    }

    $_SESSION['alert'] = 'Student deleted successfully.';

    header("Location: index.php?section=students");
    exit;
}

// BOOKS CREATE
if ($section === 'books' && $action === 'create') {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $title    = trim($_POST['book_title'] ?? '');
        $author   = trim($_POST['book_author'] ?? '');
        $category = trim($_POST['book_category'] ?? '');

        if ($title !== '' && $author !== '' && $category !== '') {

            $stmt = $pdo->prepare("
                INSERT INTO books (
                    book_title,
                    book_author,
                    book_category
                )
                VALUES (?, ?, ?)
            ");

            $stmt->execute([
                $title,
                $author,
                $category
            ]);

            header("Location: index.php?section=books");
            exit;
        }
    }
}

// BOOKS UPDATE
if ($section === 'books' && $action === 'update') {

    $bookId = (int) ($_GET['id'] ?? 0);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $title    = trim($_POST['book_title'] ?? '');
        $author   = trim($_POST['book_author'] ?? '');
        $category = trim($_POST['book_category'] ?? '');

        $stmt = $pdo->prepare("
            UPDATE books
            SET
                book_title = ?,
                book_author = ?,
                book_category = ?
            WHERE book_id = ?
        ");

        $stmt->execute([
            $title,
            $author,
            $category,
            $bookId
        ]);

        header("Location: index.php?section=books");
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT *
        FROM books
        WHERE book_id = ?
    ");

    $stmt->execute([$bookId]);

    $book = $stmt->fetch();

    if (!$book) {
        die("Book not found.");
    }
}

// BOOKS DELETE
if ($section === 'books' && $action === 'delete') {

    $bookId = (int) ($_GET['id'] ?? 0);

    try {

        $stmt = $pdo->prepare("
            DELETE FROM books
            WHERE book_id = ?
        ");

        $stmt->execute([$bookId]);

    } catch (PDOException $e) {

        echo "<p>Cannot delete this book because it has borrow records.</p>";
        echo "<p><a href='index.php?section=books'>Back</a></p>";
        exit;
    }

    header("Location: index.php?section=books");
    exit;
}


// BORROW CREATE
if ($section === 'borrow' && $action === 'create') {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $studentId = (int) ($_POST['student_id'] ?? 0);
        $bookId    = (int) ($_POST['book_id'] ?? 0);

        if ($studentId > 0 && $bookId > 0) {

            $stmt = $pdo->prepare("
                INSERT INTO borrow (
                    student_id,
                    book_id
                )
                VALUES (?, ?)
            ");

            $stmt->execute([
                $studentId,
                $bookId
            ]);

            $_SESSION['alert'] = 'Book borrowed successfully.';

            header("Location: index.php?section=borrow");
            exit;
        }
    }
}

// BORROW RETURN
if ($section === 'borrow' && $action === 'return') {

    $borrowId = (int) ($_GET['id'] ?? 0);

    $stmt = $pdo->prepare("
        UPDATE borrow
        SET borrow_return_date = CURRENT_TIMESTAMP
        WHERE borrow_id = ?
    ");

    $stmt->execute([$borrowId]);

    
    $_SESSION['alert'] = 'Book returned successfully.';

    header("Location: index.php?section=borrow");
    exit;
}

// BORROW DELETE
if ($section === 'borrow' && $action === 'delete') {

    $borrowId = (int) ($_GET['id'] ?? 0);

    $stmt = $pdo->prepare("
        DELETE FROM borrow
        WHERE borrow_id = ?
    ");

    $stmt->execute([$borrowId]);

    header("Location: index.php?section=borrow");
    exit;
}

// FETCH DATA
$students = [];
$books = [];
$borrowRecords = [];


// FETCH STUDENTS
if ($section === 'students') {

    $stmt = $pdo->query("
        SELECT *
        FROM students
        ORDER BY student_id DESC
    ");

    $students = $stmt->fetchAll();
}

// FETCH BOOKS
if ($section === 'books') {

    $stmt = $pdo->query("
        SELECT *
        FROM books
        ORDER BY book_id DESC
    ");

    $books = $stmt->fetchAll();
}

// FETCH BORROW
if ($section === 'borrow') {

    $stmt = $pdo->query("
        SELECT
            borrow.borrow_id,
            borrow.borrow_date,
            borrow.borrow_return_date,

            students.student_first_name,
            students.student_last_name,

            books.book_title,
            books.book_author

        FROM borrow

        INNER JOIN students
            ON borrow.student_id = students.student_id

        INNER JOIN books
            ON borrow.book_id = books.book_id

        ORDER BY borrow.borrow_id DESC
    ");

    $borrowRecords = $stmt->fetchAll();

    // FETCH STUDENTS FOR BORROW FORM

    $stmt = $pdo->query("
        SELECT
            student_id,
            student_first_name,
            student_last_name
        FROM students
        ORDER BY student_last_name, student_first_name
    ");

    $students = $stmt->fetchAll();

    // FETCH BOOKS FOR BORROW FORM

    $stmt = $pdo->query("
        SELECT
            book_id,
            book_title,
            book_author
        FROM books
        ORDER BY book_title
    ");

    $books = $stmt->fetchAll();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Simple PHP PDO CRUD</title>
</head>

<body>

<h1>Simple PHP PDO CRUD</h1>

<nav>
    <a href="index.php?section=students">Students</a> |
    <a href="index.php?section=books">Books</a> |
    <a href="index.php?section=borrow">Borrow</a>
</nav>

<hr>

<?php if ($section === 'students'): ?>

    <h2>Students</h2>

    <p>
        <a href="index.php?section=students&action=create">
            Add Student
        </a>
    </p>

    <?php if ($action === 'create'): ?>

        <h3>Add Student</h3>

        <form method="POST">

            <p>
                <label>First Name:</label><br>
                <input
                    type="text"
                    name="student_first_name"
                    required
                >
            </p>

            <p>
                <label>Last Name:</label><br>
                <input
                    type="text"
                    name="student_last_name"
                    required
                >
            </p>

            <p>
                <label>Course:</label><br>
                <input
                    type="text"
                    name="student_course"
                    required
                >
            </p>

            <button type="submit">
                Save
            </button>

            <a href="index.php?section=students">
                Cancel
            </a>

        </form>

    <?php elseif ($action === 'update'): ?>

        <h3>Update Student</h3>

        <form method="POST">

            <p>
                <label>First Name:</label><br>
                <input
                    type="text"
                    name="student_first_name"
                    value="<?= htmlspecialchars($student['student_first_name']) ?>"
                    required
                >
            </p>

            <p>
                <label>Last Name:</label><br>
                <input
                    type="text"
                    name="student_last_name"
                    value="<?= htmlspecialchars($student['student_last_name']) ?>"
                    required
                >
            </p>

            <p>
                <label>Course:</label><br>
                <input
                    type="text"
                    name="student_course"
                    value="<?= htmlspecialchars($student['student_course']) ?>"
                    required
                >
            </p>

            <button type="submit">
                Update
            </button>

            <a href="index.php?section=students">
                Cancel
            </a>

        </form>

    <?php else: ?>

        <table border="1" cellpadding="8">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Course</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($students as $student): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($student['student_id']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($student['student_first_name']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($student['student_last_name']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($student['student_course']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($student['student_created_at']) ?>
                        </td>

                        <td>

                            <a href="index.php?section=students&action=update&id=<?= $student['student_id'] ?>">
                                Edit
                            </a>

                            |

                            <a
                                href="index.php?section=students&action=delete&id=<?= $student['student_id'] ?>"
                                onclick="return confirm('Delete this student?');"
                            >
                                Delete
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>


<?php elseif ($section === 'books'): ?>

    <h2>Books</h2>

    <p>
        <a href="index.php?section=books&action=create">
            Add Book
        </a>
    </p>

    <?php if ($action === 'create'): ?>

        <h3>Add Book</h3>

        <form method="POST">

            <p>
                <label>Title:</label><br>
                <input
                    type="text"
                    name="book_title"
                    required
                >
            </p>

            <p>
                <label>Author:</label><br>
                <input
                    type="text"
                    name="book_author"
                    required
                >
            </p>

            <p>
                <label>Category:</label><br>
                <input
                    type="text"
                    name="book_category"
                    required
                >
            </p>

            <button type="submit">
                Save
            </button>

            <a href="index.php?section=books">
                Cancel
            </a>

        </form>

    <?php elseif ($action === 'update'): ?>

        <h3>Update Book</h3>

        <form method="POST">

            <p>
                <label>Title:</label><br>
                <input
                    type="text"
                    name="book_title"
                    value="<?= htmlspecialchars($book['book_title']) ?>"
                    required
                >
            </p>

            <p>
                <label>Author:</label><br>
                <input
                    type="text"
                    name="book_author"
                    value="<?= htmlspecialchars($book['book_author']) ?>"
                    required
                >
            </p>

            <p>
                <label>Category:</label><br>
                <input
                    type="text"
                    name="book_category"
                    value="<?= htmlspecialchars($book['book_category']) ?>"
                    required
                >
            </p>

            <button type="submit">
                Update
            </button>

            <a href="index.php?section=books">
                Cancel
            </a>

        </form>

    <?php else: ?>

        <table border="1" cellpadding="8">

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($books as $book): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($book['book_id']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($book['book_title']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($book['book_author']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($book['book_category']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($book['book_created_at']) ?>
                        </td>

                        <td>

                            <a href="index.php?section=books&action=update&id=<?= $book['book_id'] ?>">
                                Edit
                            </a>

                            |

                            <a
                                href="index.php?section=books&action=delete&id=<?= $book['book_id'] ?>"
                                onclick="return confirm('Delete this book?');"
                            >
                                Delete
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>


<?php elseif ($section === 'borrow'): ?>

    <h2>Borrow Records</h2>

    <p>
        <a href="index.php?section=borrow&action=create">
            Borrow a Book
        </a>
    </p>


    <?php if ($action === 'create'): ?>

        <h3>Borrow Book</h3>

        <form method="POST">

            <p>

                <label>Student:</label><br>

                <select name="student_id" required>

                    <option value="">
                        -- Select Student --
                    </option>

                    <?php foreach ($students as $student): ?>

                        <option value="<?= $student['student_id'] ?>">

                            <?= htmlspecialchars(
                                $student['student_first_name']
                                . ' '
                                . $student['student_last_name']
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </p>


            <p>

                <label>Book:</label><br>

                <select name="book_id" required>

                    <option value="">
                        -- Select Book --
                    </option>

                    <?php foreach ($books as $book): ?>

                        <option value="<?= $book['book_id'] ?>">

                            <?= htmlspecialchars(
                                $book['book_title']
                                . ' - '
                                . $book['book_author']
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </p>


            <button type="submit">
                Borrow
            </button>

            <a href="index.php?section=borrow">
                Cancel
            </a>

        </form>


    <?php else: ?>

        <table border="1" cellpadding="8">

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Book</th>
                    <th>Borrow Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>

            </thead>


            <tbody>

                <?php foreach ($borrowRecords as $borrow): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($borrow['borrow_id']) ?>
                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $borrow['student_first_name']
                                . ' '
                                . $borrow['student_last_name']
                            ) ?>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $borrow['book_title']
                            ) ?>

                            -
                            <?= htmlspecialchars(
                                $borrow['book_author']
                            ) ?>

                        </td>

                        <td>
                            <?= htmlspecialchars($borrow['borrow_date']) ?>
                        </td>

                        <td>

                            <?php if ($borrow['borrow_return_date']): ?>

                                <?= htmlspecialchars(
                                    $borrow['borrow_return_date']
                                ) ?>

                            <?php else: ?>

                                Not returned

                            <?php endif; ?>

                        </td>

                        <td>

                            <?php if ($borrow['borrow_return_date']): ?>

                                Returned

                            <?php else: ?>

                                Borrowed

                            <?php endif; ?>

                        </td>

                        <td>

                            <?php if (!$borrow['borrow_return_date']): ?>

                                <a
                                    href="index.php?section=borrow&action=return&id=<?= $borrow['borrow_id'] ?>"
                                    onclick="return confirm('Mark this book as returned?');"
                                >
                                    Return
                                </a>

                                |

                            <?php endif; ?>


                            <a
                                href="index.php?section=borrow&action=delete&id=<?= $borrow['borrow_id'] ?>"
                                onclick="return confirm('Delete this borrow record?');"
                            >
                                Delete
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

<?php endif; ?>

</body>
<?php if (isset($_SESSION['alert'])): ?>

    <script>
        alert(<?= json_encode($_SESSION['alert']) ?>);
    </script>

    <?php unset($_SESSION['alert']); ?>

<?php endif; ?>
</html>