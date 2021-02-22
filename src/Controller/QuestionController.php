<?php

namespace App\Controller;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use App\Service\MarkdownHelper;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use Psr\Log\LoggerInterface;
use Sentry\State\HubInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Twig\Environment;

class QuestionController extends AbstractController
{
    private $logger;

    private $isDebug;

    private $entityManager;

    public function __construct(LoggerInterface $logger, bool $isDebug, EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->isDebug = $isDebug;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="app_homepage")
     */
    public function homepage(QuestionRepository $questionRepository)
    {
       $questions = $questionRepository->findAllAskedOrderedByNewest();

        return $this->render('question/homepage.html.twig', ['questions' => $questions]);
    }

    /**
     * @Route("/questions/new", name="app_question_new")
     */
    public function new()
    {


        return new Response(sprintf('New client created id: %d with slug %s ', $question->getId(), $question->getSlug()));
    }

    /**
     * @Route("/questions/{slug}", name="app_question_show")
     */
    public function show(Question $question)
    {
        if ($this->isDebug) {
            $this->logger->info('We are in debug mode');
        }

        $answers = [
            'Make sure your cat is sitting `purrrfectly` still 🤣',
            'Honestly, I like furry shoes better than MY cat',
            'Maybe... try saying the spell backwards?',
        ];

        return $this->render('question/show.html.twig', [
            'answers' => $answers,
            'question' => $question
        ]);
    }

    /** @Route("/questions/{slug}/vote", name="app_question_vote", methods="POST") */
    public function questionVote(Question $question, Request $request)
    {
        $voteDirection = $request->request->get('direction');

        if ($voteDirection === 'up') {
            $question->upVote();
        } elseif($voteDirection === 'down') {
           $question->downVote();
        }

        $this->entityManager->flush();


        return $this->redirectToRoute('app_question_show', ['slug' => $question->getSlug()]);
    }

}
