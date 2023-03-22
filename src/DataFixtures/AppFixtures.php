<?php

namespace App\DataFixtures;

use App\Entity\Casting;
use App\Entity\Genre;
use App\Entity\Movie;
use App\Entity\Person;
use App\Entity\Season;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    private $movieSlug;

 
    public function __construct(SluggerInterface $sluggerInterface)
    {
        $this->movieSlug = $sluggerInterface;
    }


    public function load(ObjectManager $manager): void
    {
        // * utilisation de FakerPHP
        $faker = Factory::create('fr_FR');

        // on ajoute un provider pour avoir accès a des nouvelles données
        $faker->addProvider(new \Xylis\FakerCinema\Provider\Character($faker));


        // TODO : créer les genres
        $allGenreNames = [
            "Action",
            "Animation",
            "Aventure",
            "Comédie",
            "Dessin animé",
            "Documentaire",
            "Drame",
            "Espionnage",
            "Famille",
            "Fantastique",
            "Historique",
            "Policier",
            "Romance",
            "Science-fiction",
            "Thriller",
            "Western",
            // pour tester l'ordre d'affichage
            "Horreur"
        ];
        $allGenreObject = [];
        foreach ($allGenreNames as $genreName) {
            $newGenre = new Genre();
            $newGenre->setName($genreName);

            // ! on oublie pas le persist
            $manager->persist($newGenre);
            // je conserver les objet genre pour les utiliser pendant la création de film
            $allGenreObject[] = $newGenre;
        }

        // TODO : créer 300 films
        // je fais un tableau de film pour pouvoir les ré-utiliser plus tard (Casting)
        $allMovie = [];
        for ($i=1; $i <= 300; $i++) { 
        
            // 1. Pour créer un nouvel objet, on fait un new
            $newMovie = new Movie();
            // * je n'oublie pas de lui mettre à jour se propriétés
            $newMovie->setTitle($faker->text(25));
            $newMovie->setSynopsis($faker->paragraph());
            $newMovie->setPoster("https://picsum.photos/seed/oflix-nazca-".$i."/200/300");
            $newMovie->setRating($faker->randomFloat(1, 0, 5));
            $newMovie->setDuration($faker->numberBetween(20, 240));
            $newMovie->setSlug($this->movieSlug->slug($newMovie->getTitle(), '-'));
            // terniaire : c'est un if sur une seule ligne
            // ici je demande un chiffre aléatoire : 1 ou 2
            // si c'est 1, alors le type sera 'film', sinon le type sera 'série'
            $type = mt_rand(1,2) == 1 ? "film" : "série";
            
            $newMovie->setType($type);

            $newMovie->setSummary($faker->sentence());
            $newMovie->setReleaseDate($faker->dateTimeThisCentury());
            $newMovie->setCreatedAt(new DateTime());
            
            // les films n'ont pas de saisons
            // si le type est série, on génère les saisons
            if ($type === "série"){
                
                $nbSeason = mt_rand(1,10);
                // dump($nbSeason);
                // ! ne pas utiliser $i car c'est l'index de la boucle des films !!
                for ($j=1; $j <= $nbSeason; $j++) { 
                    // TODO : ajouter des seasons
                    $season = new Season();
                    $season->setName("Saison " . $j);
                    $season->setNbEpisode(mt_rand(3,24));
                    
                    // on fait l'association
                    $newMovie->addSeason($season);
    
                    // persist pour donner notre nouvel objet à doctrine
                    $manager->persist($season);
                }
            }

            $nbGenre = mt_rand(1,5);
            for ($k=0; $k < $nbGenre; $k++) { 
                // je chercher un genre aléatoire
                $randomGenre = $allGenreObject[mt_rand(0,count($allGenreObject)-1)];
                // j'ajoute un genre à mon film
                $newMovie->addGenre($randomGenre);
            }

            // ! il faut persist à chaque boucle
            // 2. Je demande à doctrine d'enregistrer mon nouvel objet
            // $manager est de base injecter (injection de dépendance) pour pouvoir manipuler la BDD
            // $manager c'est Doctrine
            // en langage BDD, enregistrer ce dit : persister
            // * on demande donc a $manager de faire un persist sur mon objet
            $manager->persist($newMovie);

            // je remplis mon tableau de films
            $allMovie[] = $newMovie;
        }
        
        // TODO : Person : 200
        $allPerson = [];
        for ($i=0; $i < 200; $i++) { 
            // création 
            $newPerson = new Person();
            // on renseigne les propriétés
            $newPerson->setFirstname($faker->firstName());
            $newPerson->setLastname($faker->lastName());

            // ! on persist
            $manager->persist($newPerson);

            $allPerson[] = $newPerson;
        }

        // TODO : casting
        // je veux créer au moins 3 casting par film
        // je dois le faire pour chaque film : boucle sur les films
        // pour pouvoir boucler sur les films, il me faut un tableau
        foreach ($allMovie as $movie) {
            $nbMaxCasting = mt_rand(3,5);
            for ($i=0; $i < $nbMaxCasting; $i++) { 
                // je crée un casting
                $newCasting = new Casting();
                // je renseigne ses propriétés
                $newCasting->setCreditOrder($i+1); // +1 car on commence à zéro
                $newCasting->setRole($faker->character());
                // on continue avec les associations
                $newCasting->setMovie($movie);
                // OU
                // $movie->addCasting($newCasting);
                // on mélange le tableau
                shuffle($allPerson);
                // on en prend le premier
                $newCasting->setPerson($allPerson[0]);

                // ! on persist
                $manager->persist($newCasting);
            }
        }

        // 3. Comme on est en dataMapper, il y a une transaction à valider
        // ! AVANT que les données soient réellement inscrite en BDD
        // c'est l'action flush()
        // cela veux dire : valide la transaction
        // exécute TOUTES les requetes nécessaires pour tout les persist fait auparavant.
        $manager->flush();
    }

    // ? https://symfony.com/blog/new-in-fixturesbundle-group-your-fixtures
    public static function getGroups(): array
    {
        return ['appgroup'];
    }
}
