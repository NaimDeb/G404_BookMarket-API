<?php

namespace App\DataFixtures;

use App\Entity\Annonce;
use App\Entity\Author;
use App\Entity\Genre;
use App\Entity\Image;
use App\Entity\Product;
use App\Entity\ProfessionalDetails;
use App\Entity\Type;
use App\Entity\User;
use App\Entity\UserDetails;
use App\Entity\UserTransactions;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use DateTimeImmutable;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create users first
        $users = [];
        $emails = [
            'user1@example.com', 'user2@example.com', 'user3@example.com',
            'user4@example.com', 'user5@example.com', 'user6@example.com',
            'user7@example.com', 'user8@example.com', 'user9@example.com',
            'user10@example.com'
        ];
        $usernames = [
            'bookworm', 'readinglover', 'bookcollector', 'pagelover',
            'literaturefan', 'novelreader', 'bookshelf', 'librarycard',
            'storytime', 'readingchair'
        ];
        
        // Create profile images for users
        $userImages = [];
        for ($i = 0; $i < count($emails); $i++) {
            $image = new Image();
            $image->setImgPath('https://picsum.photos/id/' . ($i + 100) . '/200/200');
            $manager->persist($image);
            $userImages[] = $image;
        }
        
        // Create users with their images
        for ($i = 0; $i < count($emails); $i++) {
            $user = new User();
            $user->setEmail($emails[$i]);
            $user->setUsername($usernames[$i]);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $user->setRoles(['ROLE_USER']);
            $user->setProfileDesc('A book enthusiast who loves reading');
            $user->setImage($userImages[$i]);
            $manager->persist($user);
            $users[] = $user;
        }

        // Create admin user with its own image
        $adminImage = new Image();
        $adminImage->setImgPath('https://picsum.photos/id/999/200/200');
        $manager->persist($adminImage);
        
        $admin = new User();
        $admin->setEmail('admin@bookmarket.com');
        $admin->setUsername('admin');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin'));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setImage($adminImage);
        $manager->persist($admin);
        $users[] = $admin;

        // Create user details
        $firstNames = ['John', 'Jane', 'Robert', 'Emily', 'William', 'Olivia', 'James', 'Sophia', 'Michael', 'Emma', 'Admin'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Admin'];
        $countries = ['United States', 'United Kingdom', 'Canada', 'Australia', 'France', 'Germany', 'Spain', 'Italy', 'Japan', 'Brazil', 'Switzerland'];
        
        foreach ($users as $index => $user) {
            $userDetails = new UserDetails();
            $userDetails->setUser($user);
            $userDetails->setAddress($index . ' Main Street, City');
            $userDetails->setPhone('+1234567890' . $index);
            $userDetails->setCountry($countries[$index % count($countries)]);
            $userDetails->setFirstName($firstNames[$index % count($firstNames)]);
            $userDetails->setLastName($lastNames[$index % count($lastNames)]);
            $manager->persist($userDetails);
        }

        // Create professional details for some users
        $companies = ['Book Emporium', 'Literary Treasures', 'Page Turner Inc.'];
        for ($i = 0; $i < 3; $i++) {
            $professional = new ProfessionalDetails();
            $professional->setUser($users[$i]);
            $professional->setCompanyName($companies[$i]);
            $professional->setCompanyAddress($i . ' Business Avenue, City');
            $professional->setCompanyPhone('+9876543210' . $i);
            $manager->persist($professional);
        }

        // Create authors
        $authors = [];
        $authorNames = [
            'J.K. Rowling', 'Stephen King', 'George R.R. Martin', 
            'Jane Austen', 'Ernest Hemingway', 'Agatha Christie',
            'Mark Twain', 'Charles Dickens', 'Leo Tolstoy', 'Virginia Woolf'
        ];
        $bios = [
            'Famous author born in England', 'Renowned for horror novels',
            'Fantasy writer from the US', 'Classic English novelist',
            'American novelist and journalist', 'Mystery writer',
            'American writer and humorist', '19th century English novelist',
            'Russian author of War and Peace', 'Modernist English writer'
        ];
        
        for ($i = 0; $i < count($authorNames); $i++) {
            $author = new Author();
            $author->setName($authorNames[$i]);
            $author->setBiography($bios[$i]);
            $manager->persist($author);
            $authors[] = $author;
        }

        // Create genres
        $genres = [];
        $genreNames = ['Fiction', 'Non-fiction', 'Science Fiction', 'Fantasy', 'Mystery', 'Thriller', 'Romance', 'Biography'];
        foreach ($genreNames as $name) {
            $genre = new Genre();
            $genre->setName($name);
            $manager->persist($genre);
            $genres[] = $genre;
        }

        // Create product types
        $types = [];
        $typeNames = ['Book', 'E-book', 'Audio Book', 'Magazine'];
        foreach ($typeNames as $name) {
            $type = new Type();
            $type->setName($name);
            $manager->persist($type);
            $types[] = $type;
        }

        // Create products with unique images
        $products = [];
        $productNames = [
            'Harry Potter and the Philosopher\'s Stone', 'The Shining', 'A Game of Thrones',
            'Pride and Prejudice', 'The Old Man and the Sea', 'Murder on the Orient Express',
            'The Adventures of Tom Sawyer', 'Great Expectations', 'War and Peace', 'Mrs Dalloway',
            'To Kill a Mockingbird', '1984', 'The Great Gatsby', 'Moby Dick', 'The Catcher in the Rye',
            'Brave New World', 'The Lord of the Rings', 'Crime and Punishment', 'The Odyssey', 'Jane Eyre',
            'Wuthering Heights', 'Don Quixote', 'The Divine Comedy', 'The Brothers Karamazov', 'Madame Bovary',
            'The Picture of Dorian Gray', 'The Grapes of Wrath', 'Frankenstein', 'Alice in Wonderland', 'The Iliad'
        ];
        
        for ($i = 0; $i < count($productNames); $i++) {
            // Create unique image for each product
            $productImage = new Image();
            $productImage->setImgPath('https://picsum.photos/id/' . ($i + 200) . '/200/300');
            $manager->persist($productImage);
            
            $product = new Product();
            $product->setName($productNames[$i]);
            $product->setSpecifications('Pages: ' . rand(100, 1000) . ', Language: English, Format: Paperback');
            $product->setImage($productImage);
            $product->setType($types[$i % count($types)]);
            $product->setAuthor($authors[$i % count($authors)]);
            $manager->persist($product);
            $products[] = $product;
        }

        // Create images for announcements (these can be shared)
        $announcementImages = [];
        for ($i = 0; $i < 20; $i++) {
            $image = new Image();
            $image->setImgPath('https://picsum.photos/id/' . ($i + 500) . '/300/400');
            $manager->persist($image);
            $announcementImages[] = $image;
        }

        // Create annonces
        $annonces = [];
        $conditions = ['New', 'Like New', 'Very Good', 'Good', 'Acceptable'];
        
        for ($i = 0; $i < 20; $i++) {
            $annonce = new Annonce();
            $annonce->setProduct($products[$i % count($products)]);
            $annonce->setUser($users[$i % count($users)]);
            $annonce->setPrice(rand(500, 10000));
            $annonce->setProductCondition($conditions[$i % count($conditions)]);
            
            // Create a date between 1 year ago and now
            $daysAgo = rand(0, 365);
            $date = new \DateTime();
            $date->modify("-$daysAgo days");
            $annonce->setCreatedAt(new DateTimeImmutable($date->format('Y-m-d H:i:s')));
            
            // Add 1-3 images to annonce
            $numImages = rand(1, 3);
            for ($j = 0; $j < $numImages; $j++) {
                $imgIndex = ($i + $j) % count($announcementImages);
                $annonce->addImage($announcementImages[$imgIndex]);
            }
            
            $manager->persist($annonce);
            $annonces[] = $annonce;
        }

        // Create transactions
        $statuses = ['Pending', 'Completed', 'Cancelled'];
        
        for ($i = 0; $i < 15; $i++) {
            $transaction = new UserTransactions();
            $transaction->setAnnonce($annonces[$i % count($annonces)]);
            $transaction->setUser($users[($i + 3) % count($users)]); // Different user than the seller
            
            // Create a date between 6 months ago and now
            $daysAgo = rand(0, 180);
            $date = new \DateTime();
            $date->modify("-$daysAgo days");
            $transaction->setTransactionAt(new DateTimeImmutable($date->format('Y-m-d H:i:s')));
            
            $transaction->setStatus($statuses[$i % count($statuses)]);
            $manager->persist($transaction);
        }

        $manager->flush();
    }
}