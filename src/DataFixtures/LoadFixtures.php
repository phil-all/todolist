<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

/**
 * class LoadFixtures
 * @package App\DataFixtures\ORM
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
        for ($i = 0; $i < 50; $i++) {
            $user = new User();
            $name = ($i === 0) ?  'admin' : 'user_' . $i;

            $user
                ->setUsername($name)
                ->setPassword('$2y$13$PsHRrTDnC5W5.0nZEpjen.URDZ8GF35KTRg30ang1ChTldsSh1QKu')
                ->setEmail($name . '@example.com');

            $manager->persist($user);
        }

        for ($j = 0; $j < 250; $j++) {
            $task        = new Task();
            $now         = time();
            $diff        = rand(250, 29999999);
            $date        = new \DateTime();
            $publishDate = $date->setTimestamp(rand($now - $diff, $now));

            $task
                ->setCreatedAt($publishDate)
                ->setTitle('Tache #' . rand(15, 500))
                ->setContent($this->getRandomContent())
                ->setIsDone($this->getRandomBoolean());

                $manager->persist($task);
        }

        $manager->flush();
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

    /**
     * Get a random boolean
     *
     * @return boolean
     */
    private function getRandomBoolean(): bool
    {
        return rand(0, 1) === 1 ? true : false;
    }
}
