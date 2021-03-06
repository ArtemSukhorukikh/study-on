<?php

namespace App\Tests;

use App\DataFixtures\CourseFixtures;
use App\Entity\Course;
use App\Entity\Lesson;
use App\Tests\Mock\BillingClientMock;

class LessonTest extends AbstractTest
{

    public function testNewLesson(): void
    {
        $auth = new Auth();
        $crawler = $auth->login();
        $client = self::getClient();
        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $lesssomRepository = self::getEntityManager()->getRepository(Lesson::class);
        $course = $courseRepository->findOneBy([]);
        $courseCount = count($course->getLessons());
        $crawler = $client->request('GET', '/lessons/new/' . $course->getId());
        $this->assertResponseOk();
        $buttonCrawlerNode = $crawler->selectButton('Сохранить');
        $form = $buttonCrawlerNode->form();
        $client->submit(
            $form,
            [
                'lesson[name]' => 'ТестовоеНазвание',
                'lesson[content]' => 'Test',
                'lesson[number]' => 5,
            ]
        );
        $countLessons = count($lesssomRepository->findBy(['course' => $course]));
        self::assertEquals('ТестовоеНазвание', $lesssomRepository->findOneBy(['name' => 'ТестовоеНазвание']));
        self::assertEquals($courseCount + 1, $countLessons);
    }

    public function testEditLesson(): void
    {
        $auth = new Auth();
        $crawler = $auth->login();
        $client = self::getClient();
        $lessonRepository = self::getEntityManager()->getRepository(Lesson::class);
        $crawler = $client->request('GET', '/lessons/' . $lessonRepository->findAll()[0]->getId() . '/edit');
        $this->assertResponseOk();
        $buttonCrawlerNode = $crawler->selectButton('Изменить');
        $form = $buttonCrawlerNode->form();
        $client->submit(
            $form,
            [
                'lesson[name]' => 'ТестовоеНазвание',
                'lesson[content]' => 'Test',
                'lesson[number]' => 5,
            ]
        );
        $this->assertResponseRedirect();
        self::assertEquals('ТестовоеНазвание', $lessonRepository->findOneBy(['name' => 'ТестовоеНазвание']));
    }

    public function testDeleteLesson(): void
    {
        $auth = new Auth();
        $crawler = $auth->login();
        $client = self::getClient();
        $lessonRepository = self::getEntityManager()->getRepository(Lesson::class);
        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $countOld = count($lessonRepository->findAll());
        $crawler = $client->request(
            'GET',
            '/lessons/' . $lessonRepository->findBy(
                ['course' => $courseRepository->findBy(['code' => 'uid3'])[0]]
            )[0]->getId()
        );
        $this->assertResponseOk();
        $client->submitForm('lesson-delete');
        $this->assertResponseRedirect();
        self::getEntityManager()->clear();
        $countNew = count($lessonRepository->findAll());
        self::assertEquals($countOld - 1, $countNew);
    }

    public function testEmptyLesson(): void
    {
        $auth = new Auth();
        $crawler = $auth->login();
        $client = self::getClient();
        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $lessonRepository = self::getEntityManager()->getRepository(Lesson::class);
        $countOld = $this->count($lessonRepository->findAll());
        $crawler = $client->request('GET', '/lessons/new/' . $courseRepository->findAll()[0]->getId());
        $this->assertResponseOk();
        $buttonCrawlerNode = $crawler->selectButton('Сохранить');
        $form = $buttonCrawlerNode->form();
        $client->submit(
            $form,
            [
                'lesson[name]' => '',
                'lesson[content]' => 'Test',
                'lesson[number]' => 5,
            ]
        );
        $this->assertResponseCode(422);
        $countNew = $this->count($lessonRepository->findAll());
        self::assertEquals($countOld, $countNew);
        $client->submit(
            $form,
            [
                'lesson[name]' => 'test',
                'lesson[content]' => '',
                'lesson[number]' => 5,
            ]
        );
        $this->assertResponseCode(422);
        $countNew = $this->count($lessonRepository->findAll());
        self::assertEquals($countOld, $countNew);
        $client->submit(
            $form,
            [
                'lesson[name]' => 'Test',
                'lesson[content]' => 'Test',
                'lesson[number]' => null,
            ]
        );
        $this->assertResponseCode(422);
        $countNew = $this->count($lessonRepository->findAll());
        self::assertEquals($countOld, $countNew);
    }

    public function testValidateLesson(): void
    {
        $auth = new Auth();
        $crawler = $auth->login();
        $client = self::getClient();
        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $lessonRepository = self::getEntityManager()->getRepository(Lesson::class);
        $countOld = $this->count($lessonRepository->findAll());
        $crawler = $client->request('GET', '/lessons/new/' . $courseRepository->findAll()[0]->getId());
        $this->assertResponseOk();
        $buttonCrawlerNode = $crawler->selectButton('Сохранить');
        $form = $buttonCrawlerNode->form();
        $client->submit(
            $form,
            [
                'lesson[name]' => 'testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest',
                'lesson[content]' => 'test',
                'lesson[number]' => 5,
            ]
        );
        $this->assertResponseCode(422);
        $countNew = $this->count($lessonRepository->findAll());
        self::assertEquals($countOld, $countNew);
        $client->submit(
            $form,
            [
                'lesson[name]' => 'Test',
                'lesson[content]' => 'Test',
                'lesson[number]' => 999999999999999999999999999999999999999999,
            ]
        );
        $this->assertResponseCode(422);
        $countNew = $this->count($lessonRepository->findAll());
        self::assertEquals($countOld, $countNew);
    }

    public function getFixtures(): array
    {
        return [
            CourseFixtures::class,
        ];
    }
}
