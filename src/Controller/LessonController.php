<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Form\LessonType;
use App\Repository\LessonRepository;
use App\Service\BillingClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/lessons')]
class LessonController extends AbstractController
{
//    #[Route('/', name: 'app_lesson_index', methods: ['GET'])]
//    public function index(LessonRepository $lessonRepository): Response
//    {
//        return $this->render('lesson/index.html.twig', [
//            'lessons' => $lessonRepository->findAll(),
//        ]);
//    }

    #[Route('/new/{course}', name: 'app_lesson_new', methods: ['GET', 'POST'])]
    public function new(Request $request, LessonRepository $lessonRepository, Course $course): Response
    {
        $lesson = new Lesson();

        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $lesson->setCourse($course);
            $lessonRepository->add($lesson);
            return $this->redirectToRoute(
                'app_course_show',
                ['id' => $lesson->getCourse()->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm('lesson/new.html.twig', [
            'lesson' => $lesson,
            'form' => $form,
            'course' => $course,
        ]);
    }

    #[Route('/{id}', name: 'app_lesson_show', methods: ['GET'])]
    public function show(Lesson $lesson, BillingClient $client): Response
    {
        $transaction = $client->getTransactions(
            ['type' => 'payment', 'course_code' => $lesson->getCourse()->getCode(), 'skip_expired' => true],
            $this->getUser()->getApiToken()
        );
        if (!$transaction && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw new HttpException(Response::HTTP_NOT_ACCEPTABLE, 'У Вас нет доступа к данному курсу');
        }
        return $this->render('lesson/show.html.twig', [
            'lesson' => $lesson,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_lesson_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Lesson $lesson, LessonRepository $lessonRepository): Response
    {
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lessonRepository->add($lesson);
            return $this->redirectToRoute(
                'app_course_show',
                ['id' => $lesson->getCourse()->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm('lesson/edit.html.twig', [
            'lesson' => $lesson,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_lesson_delete', methods: ['POST'])]
    public function delete(Request $request, Lesson $lesson, LessonRepository $lessonRepository): Response
    {
        $courseId = $lesson->getCourse()->getId();
        if ($this->isCsrfTokenValid('delete' . $lesson->getId(), $request->request->get('_token'))) {
            $lessonRepository->remove($lesson);
        }

        return $this->redirectToRoute('app_course_show', ['id' => $courseId], Response::HTTP_SEE_OTHER);
    }
}
