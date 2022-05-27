<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

/**
 * class LoadFixtures
 * @package App\DataFixtures\ORM
 * @codeCoverageIgnore
 */
class LoadFixtures extends Fixture
{
    /**
     * Load fixtures
     *
     * @param ObjectManager $manager
     *
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $this->createAnonymousTasks($manager);

        $this->createUsersAndTasks($manager);

        $manager->flush();
    }

    /**
     * Create and persist anonymous tasks
     *
     * @param ObjectManager $manager
     *
     * @return void
     */
    private function createAnonymousTasks(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) {
            $task        = new Task();
            $now         = time();
            $diff        = rand(250, 29999999);
            $date        = new DateTime();
            $publishDate = $date->setTimestamp(rand($now - $diff, $now));

            $task
                ->setCreatedAt($publishDate)
                ->setTitle('Tâche #' . ($i + 1))
                ->setContent($this->getRandomContent())
                ->setIsDone($i < 5 ? true : false);

            $manager->persist($task);
        }
    }

    private function createAttributedTasks(ObjectManager $manager, User $user, int $userIndex): void
    {
        for ($i = 0; $i < 3; $i++) {
            $task        = new Task();
            $now         = time();
            $diff        = rand(250, 29999999);
            $date        = new DateTime();
            $publishDate = $date->setTimestamp(rand($now - $diff, $now));

            $task
                ->setCreatedAt($publishDate)
                ->setTitle('Tâche #' . (rand(11, 100)))
                ->setContent($this->getRandomContent())
                ->setIsDone($i % 2 ? true : false)
                ->setUser($user);

            $manager->persist($task);
        }
    }

    /**
     * Create and persist users and their tasks
     *
     * @param ObjectManager $manager
     *
     * @return void
     */
    private function createUsersAndTasks(ObjectManager $manager): void
    {
        for ($j = 0; $j < 10; $j++) {
            $user = new User();
            $name = ($j === 0) ?  'admin_1' : 'user_' . ($j + 1);

            $user
                ->setUsername($name)
                ->setPassword('$2y$13$PsHRrTDnC5W5.0nZEpjen.URDZ8GF35KTRg30ang1ChTldsSh1QKu')
                ->setEmail($name . '@example.com')
                ->setRoles(($j === 0) ? ['ROLE_ADMIN'] : ['ROLE_USER']);

            $manager->persist($user);

            if ($user->getUsername() !== 'admin_1') {
                $this->createAttributedTasks($manager, $user, $j);
            }
        }
    }

    /**
     * Get task random content
     *
     * @return string
     */
    private function getRandomContent(): string
    {
        $content = [
            'Deinde qui fit, ut ego nesciam, sciant omnes.',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'Ergo ita: non posse honeste vivi, nisi honeste vivatur.',
            'Rationis enim perfectio est virtus; Quid sequatur, quid repugnet, vident.',
            'Istic sum, inquit.',
            'Sed quid sentiat, non videtis.',
            'Sed tamen intellego quid velit.',
            'ur deinde Metrodori liberos commendas? Suo enim quisque studio maxime ducitur.',
            'Utilitatis causa amicitia est quaesita.',
            'Itaque hic ipse iam pridem est reiectus',
            'Nam ante Aristippus, et ille melius.'
        ];

        return $content[rand(0, count($content) - 1)];
    }
}
