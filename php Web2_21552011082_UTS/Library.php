<?php

class Library {
    private $books = [];
    private $borrowedBooks = [];
    private $lateFeePerDay = 1000;

    public function addBook(Book $book) {
        $this->books[] = $book;
    }

    public function borrowBook($isbn, $borrowerName, $borrowDate) {
        $maxBooksPerUser = 3;

        if (count($this->borrowedBooks) < $maxBooksPerUser) {
            foreach ($this->books as $book) {
                if ($book->getISBN() === $isbn && !$book->isBorrowed()) {
                    $book->borrowBook();
                    $this->borrowedBooks[] = [
                        'book' => $book,
                        'borrower_name' => $borrowerName,
                        'borrow_date' => $borrowDate
                    ];
                    return true;
                }
            }
        }
        return false;
    }
    public function returnBook($isbn) {
        foreach ($this->borrowedBooks as $key => $borrowedBook) {
            if ($borrowedBook['book']->getISBN() === $isbn) {
                $borrowedBook['book']->returnBook();
                unset($this->borrowedBooks[$key]);
                return true;
            }
        }
        return false;
    }

    public function getBorrowedBooks() {
        return $this->borrowedBooks;
    }

    public function removeBook($isbn) {
        foreach ($this->books as $key => $book) {
            if ($book->getISBN() === $isbn) {
                unset($this->books[$key]);
                $this->removeFromBorrowedBooks($book);
                return true;
            }
        }
        return false;
    }

    private function removeFromBorrowedBooks($book) {
        foreach ($this->borrowedBooks as $key => $borrowedBook) {
            if ($book === $borrowedBook) {
                unset($this->borrowedBooks[$key]);
                return;
            }
        }
    }

    public function sortBooksByYear() {
        usort($this->books, function($a, $b) {
            return $a->getYear() <=> $b->getYear();
        });
    }

    public function sortBooksByAuthor() {
        usort($this->books, function($a, $b) {
            return strcmp($a->getAuthor(), $b->getAuthor());
        });
    }

    public function calculateLateFee($isbn, $returnDate) {
        $lateFee = 0;

        foreach ($this->books as $book) {
            if ($book->getISBN() === $isbn && $book->isBorrowed()) {
                $dueDate = date('Y-m-d', strtotime('+7 days', strtotime($returnDate)));
                $currentDate = date('Y-m-d');
                if ($currentDate > $dueDate) {
                    $daysLate = floor((strtotime($currentDate) - strtotime($dueDate)) / (60 * 60 * 24));
                    $lateFee = $daysLate * $this->lateFeePerDay;
                }
                break;
            }
        }

        return $lateFee;
    }

    public function calculateLimit() {
        $borrowPeriodDays = 7;
        return date('Y-m-d', strtotime("+$borrowPeriodDays days"));
    }

    public function getBooks() {
        return $this->books;
    }
}

?>