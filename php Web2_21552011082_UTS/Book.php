<?php

class Book {
    private $isbn;
    private $title;
    private $author;
    private $publisher;
    private $year;
    private $isBorrowed;

    public function __construct($isbn, $title, $author, $publisher, $year) {
        $this->isbn = $isbn;
        $this->title = $title;
        $this->author = $author;
        $this->publisher = $publisher;
        $this->year = $year;
        $this->isBorrowed = false;
    }

    public function getISBN() {
        return $this->isbn;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function getPublisher() {
        return $this->publisher;
    }

    public function getYear() {
        return $this->year;
    }

    public function isBorrowed() {
        return $this->isBorrowed;
    }

    public function borrowBook() {
        $this->isBorrowed = true;
    }

    public function returnBook() {
        $this->isBorrowed = false;
    }
}

class ReferenceBook extends Book {
    private $isbn;
    private $publisher;

    public function __construct($isbn, $title, $author, $publisher, $year) {
        parent::__construct($isbn, $title, $author, $publisher, $year);
        $this->isbn = $isbn;
        $this->publisher = $publisher;
    }

    public function getISBN() {
        return $this->isbn;
    }

    public function getPublisher() {
        return $this->publisher;
    }
}

?>