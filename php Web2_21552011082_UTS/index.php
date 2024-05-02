<?php
require_once "Library.php";
require_once "Book.php";
session_start();


$library = null;

if (!isset($_SESSION['library'])) {
    $_SESSION['library'] = new Library();
}

$library = $_SESSION['library'];


if (isset($_POST['addBook'])) {
    $isbn = $_POST['isbn'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $publisher = $_POST['publisher'];
    $year = $_POST['year'];

    $newBook = new Book($isbn, $title, $author, $publisher, $year);
    $library->addBook($newBook);
}

$searchResults = [];
if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $searchResults = array_filter($library->getBooks(), function($book) use ($searchTerm) {
        return stripos($book->getTitle(), $searchTerm) !== false || stripos($book->getAuthor(), $searchTerm) !== false;
    });
}
$searchTerm = isset($_GET['search']) ? $_GET['search'] : null;
$searchResults = [];


if (isset($_POST['borrowBook'])) {
    $isbn = $_POST['isbn'];
    $borrowerName = $_POST['borrower_name'];
    $borrowDate = $_POST['borrow_date'];

    $borrowed = $library->borrowBook($isbn, $borrowerName, $borrowDate);
    if ($borrowed) {
        $returnDate = $library->calculateLimit();
        echo "<script>alert('Buku berhasil dipinjam oleh $borrowerName. Tanggal batas pengembalian: $returnDate');</script>";
    } else {
        echo "<script>alert('Buku tidak tersedia untuk dipinjam atau batas peminjaman sudah mencapai maksimum.');</script>";
    }
}

if (isset($_POST['returnBook'])) {
    $isbn = $_POST['isbn'];
    
    $returnDate = date('Y-m-d');

    $returned = $library->returnBook($isbn);
    if ($returned) {
        $lateFee = $library->calculateLateFee($isbn, $returnDate);
        echo "<script>alert('Buku berhasil dikembalikan. Denda keterlambatan: $lateFee');</script>";
    } else {
        echo "<script>alert('Buku tidak ada dalam daftar buku yang dipinjam.');</script>";
    }
}



if (isset($_GET['deleteBook'])) {
    $isbn = $_GET['isbn'];
    $deleted = $library->removeBook($isbn);
    if ($deleted) {
        echo "<script>alert('Buku berhasil dihapus.');</script>";
    } else {
        echo "<script>alert('Buku tidak ditemukan dalam koleksi.');</script>";
    }
}

if ($searchTerm) {
    $searchResults = array_filter($library->getBooks(), function($book) use ($searchTerm) {
        return stripos($book->getTitle(), $searchTerm) !== false || stripos($book->getAuthor(), $searchTerm) !== false;
    });
} else {
    $searchResults = $library->getBooks();
}

$sortOption = isset($_GET['sort']) ? $_GET['sort'] : null;
if ($sortOption === 'author') {
    usort($searchResults, function($a, $b) {
        return strcmp($a->getAuthor(), $b->getAuthor());
    });
} elseif ($sortOption === 'year') {
    usort($searchResults, function($a, $b) {
        return $a->getYear() - $b->getYear();
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Perpustakaan</title>
<link rel="stylesheet" type="text/css" href="styles.css">
<link rel="icon" type="image/png" href="images/heart.png">
</head>
<body>

<div class="container">
    <div class="plat">
        <h2>PERPUSTAKAAN SHASKIA</h2>
                <h3>Daftar Buku Tersedia</h3>
                <form action="" method="get">
                    <select name="sort">
                        <option value="">Urutkan</option>
                        <option value="author">Penulis</option>
                        <option value="year">Tahun Terbit</option>
                    </select>
                    <input type="submit" value="Sortir">
                </form>

    <table>
        <tr>
            <th>ISBN</th>
            <th>Judul</th>
            <th>Penulis</th>
            <th>Penerbit</th>
            <th>Tahun Terbit</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($searchResults as $book): ?>
            <tr>
                <td><?php echo $book->getISBN(); ?></td>
                <td><?php echo $book->getTitle(); ?></td>
                <td><?php echo $book->getAuthor(); ?></td>
                <td><?php echo $book->getPublisher(); ?></td>
                <td><?php echo $book->getYear(); ?></td>
                <td>
                    <!-- Button to borrow book -->
                    <button onclick="showBorrowForm('<?php echo $book->getISBN(); ?>')">Pinjam</button>
                    <!-- Button to delete book -->
                    <a href="?deleteBook&isbn=<?php echo $book->getISBN(); ?>"
                       onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?')">Hapus</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <form action="" method="get">
    <h3>Cari Buku</h3>
    <label for="search">Judul atau Penulis:</label>
    <input type="text" id="search" name="search">
    <input type="submit" value="Cari">
</form>

<h3>Daftar Buku yang Dipinjam</h3>
    <table>
        <tr>
            <th>Nama Peminjam</th>
            <th>Judul Buku</th>
            <th>Tanggal Pinjam</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($library->getBorrowedBooks() as $borrowedBook): ?>
            <tr>
                <td><?php echo $borrowedBook['borrower_name']; ?></td>
                <td><?php echo $borrowedBook['book']->getTitle(); ?></td>
                <td><?php echo $borrowedBook['borrow_date']; ?></td>
                <td>
                    <form action="" method="post">
                        <input type="hidden" name="isbn" value="<?php echo $borrowedBook['book']->getISBN(); ?>">
                        <input type="submit" name="returnBook" value="Kembalikan">
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

<div id="borrowForm" style="display: none;">
    <form action="" method="post">
        <input type="hidden" id="borrowIsbn" name="isbn" value="">
        <label for="borrower_name">Nama Peminjam:</label>
        <input type="text" id="borrower_name" name="borrower_name" required>
        <label for="borrow_date">Tanggal Pinjam:</label>
        <input type="date" id="borrow_date" name="borrow_date" required>
        <input type="submit" name="borrowBook" value="Pinjam">
    </form>
</div>

<script>
    function showBorrowForm(isbn) {
        document.getElementById('borrowIsbn').value = isbn;
        document.getElementById('borrowForm').style.display = 'block';
    }
</script>


    <form action="" method="post">
        <h3>Tambah Buku Baru</h3>
        <label for="isbn">ISBN:</label>
        <input type="text" id="isbn" name="isbn" required>
        <label for="title">Judul:</label>
        <input type="text" id="title" name="title" required>
        <label for="author">Penulis:</label>
        <input type="text" id="author" name="author" required>
        <label for="publisher">Penerbit:</label>
        <input type="text" id="publisher" name="publisher" required>
        <label for="year">Tahun Terbit:</label>
        <input type="number" id="year" name="year" min="1900" max="<?php echo date('Y'); ?>" required>
        <input type="submit" name="addBook" value="Tambahkan Buku">
    </form>
</div>


</body>
</html>
