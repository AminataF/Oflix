<?php

namespace App\Controller\Api;

use App\Entity\Genre;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GenreController extends AbstractController
{
    /**
     * @Route("/api/genres", name="app_api_genres", methods={"GET"})
     */
    public function browse(GenreRepository $genreRepository): JsonResponse
    {
        $allGenres = $genreRepository->findAll();
        // return $this->json([
        //     'message' => 'Welcome to your new controller!',
        //     'path' => 'src/Controller/Api/GenreController.php',
        // ]);
        // data = $allgenre, 200 = code http (reponse::HTTP_OK), les entête = header, et enfin les groupes
        return $this->json($allGenres, 200, [], [

            "groups" => [

                "genre_browse",
            ]
        ]);
    }

    /**
     * @Route("/api/genre/{genreId}", name="app_api_genre_read", requirements={"genreId" = "\d+"}, methods={"GET"})
     */
    public function read($genreId, GenreRepository $genreRepository): JsonResponse
    {
        $genre = $genreRepository->find($genreId);
        // data = $allgenre, 200 = code http (reponse::HTTP_OK), les entête = header, et enfin les groupes
        return $this->json($genre, Response::HTTP_FOUND, [], [

            "groups" => [

                "genre_read",
                "movie_read"
            ]
        ]);

    }


    /**
     * 
     * @Route("/api/genre", name="app_api_genre_add", methods={"POST"})
     */
    public function add(
        Request $request,
        SerializerInterface $serializer, 
        GenreRepository $genreRepository,
        ValidatorInterface $validator
        )
    {
        // TODO : récupérer des infos venant du front
        // notre protocole de communication est JSON
        // ? où est le JSON que va nous envoyer le front : dans la requête : Request
        $contentJson = $request->getContent();

        // PHP nous propose une méthode pour transformer du JSON en variable PHP : json_decode()
        // ce n'est pas suffisant pour nous, on veut une Entité
        // Symfony à un service pour ça : SerializerInterface
        
        // ! Control character error, possibly incorrectly encoded
        try {
            $genreFromJson = $serializer->deserialize(
                // 1. le json
                $contentJson,
                // 2. le type, càd la classe Entité
                Genre::class,
                // 3. le format de données
                'json'
                // 4. le contexte, pour l'instant rien à y mettre
            );
            // je précise un type d'erreur en précisant la classe dans le catch
            // ici j'attrape litéralement tout ce qui se 'lance', càd tout les types d'erreurs
            // j'aurais pu préciser NotEncodableValueException
        // * } catch (NotEncodableValueException $e){
            // ici on aura que les erreurs de type NotEncodableValueException
        } catch (\Throwable $e){
            // notre exception est dans $e
            // dd($e);
            // TODO avertir l'utilisateur
            return $this->json(
                // 1. le message d'erreur
                $e->getMessage(),
                // 2. le code approprié : 422
                Response::HTTP_UNPROCESSABLE_ENTITY

            );
        }
        
        // on débug : c'est une Entité
        // dd($genreFromJson);

        // TODO : on valide les données avant de persist/flush
        // ? https://symfony.com/doc/5.4/validation.html#using-the-validator-service
        $listError = $validator->validate($genreFromJson);

        if (count($listError) > 0){
            // On a des erreurs de validation
            return $this->json(
                // 1. le contenu : la liste des erreurs
                $listError,
                // 2. un code approprié : 422
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // il ne reste plus qu'a faire persist + flush
        // merci baptiste
        $genreRepository->add($genreFromJson, true);
        // dd($genreFromJson);
        
        // il faut tenir au courant notre utilisateur
        // le code http approprié : 201 => Response::HTTP_CREATED
        return $this->json(
            // 1. l'objet créé
            $genreFromJson,
            // 2. on change le code pour un 201
            Response::HTTP_CREATED,
            // 3. pas d'entetes particulières
            [],
            // 4. comme on serialize, il faut utiliser les groups
            [
                "groups" => 
                [
                    "genre_read",
                    "movie_read"
                ]
            ]
        );
    }

    /**
     * @Route("/api/genre/{id}", name="app_api_genre_edit", methods={"PUT", "PATCH"}, requirements={"id"="\d+"})
     */
    public function edit(
        Genre $genre = null, 
        Request $request, 
        SerializerInterface $serializer, 
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
        )
    {
        // TODO : on modifie une entité
        // 1. l'entité à modifier : paramètre de route
        if ($genre === null){
            // le paramConverter n'a pas trouvé l'entité : 404
            return $this->json("Genre non trouvé", Response::HTTP_NOT_FOUND);
        }
        // 2. les informations de la requete
        $jsonContent = $request->getContent();

        // 3. je déserialize
        try {
            $genreUpdate = $serializer->deserialize(
                $jsonContent,
                Genre::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $genre]
            );
        } catch (\Throwable $e){
            // notre exception est dans $e
            // dd($e);
            // TODO avertir l'utilisateur
            return $this->json(
                // 1. le message d'erreur
                $e->getMessage(),
                // 2. le code approprié : 422
                Response::HTTP_UNPROCESSABLE_ENTITY

            );
        }
        // il faut faire l'association entre TOUTE les propriétés
        // là encore ça va 1 prop, mais avec Movie :'(
        // $genre->setName($genreUpdate->getName());
        // * on utilise donc une option du serializer pour qu'il nous mettes à jour notre entité
        // ? https://symfony.com/doc/current/components/serializer.html#deserializing-in-an-existing-object
        // un peu comme le handleRequest d'un formulaire

        // TODO : on valide les données avant de persist/flush
        // ? https://symfony.com/doc/5.4/validation.html#using-the-validator-service
        $listError = $validator->validate($genre);

        if (count($listError) > 0){
            // On a des erreurs de validation
            return $this->json(
                // 1. le contenu : la liste des erreurs
                $listError,
                // 2. un code approprié : 422
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // * ici mon objet $genre a été modifié
        // un flush est tout est bon
        $entityManager->flush();
        
        // TODO : return json
        return $this->json(
            // aucune donnée à renvoyer, puisuque c'est juste une mise à jour
            null,
            // le code approprié
            Response::HTTP_NO_CONTENT
        );
    }
    
}
