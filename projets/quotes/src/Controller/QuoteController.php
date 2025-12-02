<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuoteController extends AbstractController
{
    private array $quotes = [
        "Le succès est la somme de petits efforts répétés.",
        "La simplicité est la sophistication suprême.",
        "La motivation vous fait démarrer, l'habitude vous fait continuer.",
        "La chance sourit aux audacieux.",
        "Le seul endroit où le succès vient avant le travail est dans le dictionnaire.",
        "Ne regardez pas l'horloge; faites ce qu'elle fait. Continuez.",
        "Le futur appartient à ceux qui croient à la beauté de leurs rêves.",
        "La vie est 10% ce qui vous arrive et 90% comment vous y réagissez.",
        "Le plus grand risque est de ne prendre aucun risque.",
        "Croyez que vous pouvez et vous êtes à mi-chemin."

    ];

    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        $quote = $this->quotes[array_rand($this->quotes)];

        return $this->render('quote/home.html.twig', [
            'quote' => $quote,
            'quotes' => $this->quotes
        ]);
    }

    #[Route('/quotes', name: 'app_quotes')]
    public function quotes(): Response
    {
        return $this->render('quote/list.html.twig', [
            'quotes' => $this->quotes
        ]);
    }

    #[Route('/about', name: 'app_quote_about')]
    public function about(): Response
    {
        return $this->render('quote/about.html.twig');
    }

    #[Route('/add', name: 'app_add_quote')]
    public function add(): Response
    {
        return $this->render('quote/add.html.twig');
    }
}
