<?php

namespace App\Tests;

use App\DataFixtures\CourseFixtures;
use App\Entity\Course;
use App\Tests\AbstractTest;
use App\Tests\Mock\BillingClientMock;

class CourseTest extends AbstractTest
{
    public function testSomething(): void
    {
        $client = AbstractTest::getClient();
        $crawler = $client->request('GET', '/courses/');
        $link = $crawler->selectLink('Пройти')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
    }

    public function login() {
        $client = AbstractTest::getClient();
        $client->disableReboot();
        $client->getContainer()->set(
            'App\Service\BillingClient', 
            new BillingClientMock()
        );
        $crawler = $client->request('GET', '/login');
        $this->assertResponseOk();
        $buttonCrawlerNode = $crawler->selectButton('Вход');
        $form = $buttonCrawlerNode->form();
        $client->submit(
            $form,
            ['email' => 'test@mail.com', 'password' => 'test']
        );
        $crawler = $client->followRedirect();
        $crawler = $client->request('GET', '/courses/');
        return $client;
    }

    public function testLogin(): void
    {
        $client = AbstractTest::getClient();
        $client->disableReboot();
        $client->getContainer()->set(
            'App\Service\BillingClient', 
            new BillingClientMock()
        );
        $crawler = $client->request('GET', '/login');
        $this->assertResponseOk();
        $buttonCrawlerNode = $crawler->selectButton('Вход');
        $form = $buttonCrawlerNode->form();
        $client->submit(
            $form,
            ['email' => 'test@mail.com', 'password' => 'test']
        );
        $crawler = $client->followRedirect();
        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();
    }

    public function testCreatingCourse(): void
    {
        $client = $this->login();
        $crawler = $client->request('GET', '/courses/new');
        $buttonCrawlerNode = $crawler->selectButton('Сохранить');
        $form = $buttonCrawlerNode->form();
        $client->submit(
            $form,
            [
                'course[code]' => 'uuid06',
                'course[name]' => 'HTML-курс',
                'course[description]' => 'Курс по HTML',
            ]
        );
        $crawler = $client->followRedirect();
        $courseName = $crawler->filter('.course-name')->text();
        self::assertEquals('HTML-курс', $courseName);
        $courseDesq = $crawler->filter('.course-description')->text();
        self::assertEquals('Курс по HTML', $courseDesq);
    }

    public function testCountCourses(): void
    {
        $client = $this->login();
        $crawler = $client->request('GET', '/courses/');
        $countCourses = 3;
        self::assertCount($countCourses, $crawler->filter('.card-body'));
    }

    public function testCoursesPagesSuccessful(): void
    {
        $client = $this->login();
        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $coursesAll = $courseRepository->findAll();
        foreach ($coursesAll as $course) {
            $client->request('GET', '/courses/' . $course->getId());
            $this->assertResponseOk();
            $client->request('GET', '/courses/' . $course->getId() . '/edit');
            $this->assertResponseOk();
            $client->request('GET', '/lessons/new/' . $course->getId());
            $this->assertResponseOk();
        }
    }

    public function testLessonsCount(): void
    {
        $client = $this->login();
        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $coursesAll = $courseRepository->findAll();
        self::assertNotEmpty($coursesAll);
        foreach ($coursesAll as $course) {
            $crawler = $client->request('GET', '/courses/' . $course->getId());
            $this->assertResponseOk();
            self::assertCount(count($course->getLessons()), $crawler->filter('.mt-2'));
        }
    }

    public function testValidationCodeCourse(): void
    {
        $client = $this->login();
        $crawler = $client->request('GET', '/courses/new');
        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $coutseCount = $this->count($courseRepository->findAll());
        $buttonCrawlerNode = $crawler->selectButton('Сохранить');
        $form = $buttonCrawlerNode->form();
        $client->submit(
            $form,
            [
                'course[code]' => 'uid01',
                'course[name]' => 'Test',
                'course[description]' => 'Test',
            ]
        );
        $this->assertResponseRedirect();
        $coutseCountNew = $this->count($courseRepository->findAll());
        self::assertEquals($coutseCount, $coutseCountNew);
    }

    public function testValidationCourse(): void
    {
        $client = $this->login();
        $crawler = $client->request('GET', '/courses/new');
        $buttonCrawlerNode = $crawler->selectButton('Сохранить');
        $form = $buttonCrawlerNode->form();
        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $coutseCount = $this->count($courseRepository->findAll());
        $client->submit(
            $form,
            [
                'course[code]' => 'QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQW',
                'course[name]' => 'QWEQ',
                'course[description]' => 'QWEQWEQWEQWEQWEQWEQW',
            ]
        );
        $this->assertResponseCode(422);
        $coutseCountNew = $this->count($courseRepository->findAll());
        self::assertEquals($coutseCount, $coutseCountNew);
        $client->submit(
            $form,
            [
                'course[code]' => 'uid22',
                'course[name]' => 'QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQW',
                'course[description]' => 'QWEQWEQWEQWEQWEQWEQW',
            ]
        );
        $this->assertResponseCode(422);
        $coutseCountNew = $this->count($courseRepository->findAll());
        self::assertEquals($coutseCount, $coutseCountNew);
    }

    public function testWithBlankFieldsCourse(): void
    {
        $client = $this->login();
        $crawler = $client->request('GET', '/courses/new');
        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $coutseCount = $this->count($courseRepository->findAll());
        $buttonCrawlerNode = $crawler->selectButton('Сохранить');
        $form = $buttonCrawlerNode->form();
        $client->submit(
            $form,
            [
                'course[code]' => '',
                'course[name]' => 'EQW',
                'course[description]' => 'QWEQWEQWEQWEQWEQWEQW',
            ]
        );
        $this->assertResponseCode(422);
        $coutseCountNew = $this->count($courseRepository->findAll());
        self::assertEquals($coutseCount, $coutseCountNew);
        $client->submit(
            $form,
            [
                'course[code]' => 'uid22',
                'course[name]' => '',
                'course[description]' => 'QWEQWEQWEQWEQWEQWEQW',
            ]
        );
        $this->assertResponseCode(422);
        $coutseCountNew = $this->count($courseRepository->findAll());
        self::assertEquals($coutseCount, $coutseCountNew);
        $client->submit(
            $form,
            [
                'course[code]' => 'uid22',
                'course[name]' => 'QWEqwee',
                'course[description]' => '',
            ]
        );
        $this->assertResponseCode(422);
        $coutseCountNew = $this->count($courseRepository->findAll());
        self::assertEquals($coutseCount, $coutseCountNew);
    }

    public function testDeleteCourse(): void
    {
        $client = $this->login();

        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $link = $crawler->filter('.card-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $client->submitForm('course-delete');
        self::assertTrue($client->getResponse()->isRedirect('/courses/'));
        $crawler = $client->followRedirect();
        $this->assertResponseOk();

        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $courses = $courseRepository->findAll();
        self::assertNotEmpty($courses);
        $actualCoursesCount = count($courses);

        self::assertCount($actualCoursesCount, $crawler->filter('.card-body'));
    }

    public function testEditCourse(): void
    {
        $client = $this->login();

        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $link = $crawler->filter('.card-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('.course-edit')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $submitButton = $crawler->selectButton('Изменить');
        $form = $submitButton->form();
        $course = self::getEntityManager()
            ->getRepository(Course::class)
            ->findOneBy(['code' => $form['course[code]']->getValue()]);

        $form['course[code]'] = 'uid007';
        $form['course[name]'] = 'Измененный курс';
        $form['course[description]'] = 'Измененный курс';
        $client->submit($form);

        self::assertTrue($client->getResponse()->isRedirect('/courses/' . $course->getId()));
        $crawler = $client->followRedirect();
        $this->assertResponseOk();

        $courseName = $crawler->filter('.course-name')->text();
        self::assertEquals('Измененный курс', $courseName);

        $courseDescription = $crawler->filter('.course-description')->text();
        self::assertEquals('Измененный курс', $courseDescription);
    }

    public function getFixtures(): array
    {
        return [
            CourseFixtures::class,
        ];
    }
}