<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use App\Entity\Language;
use App\Entity\Currency;
use App\Entity\Country;
use App\Entity\Activity;
use App\Entity\Comment;
use App\Entity\Travel;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $countries = [
            ['name' => 'États-Unis', 'languages' => ['Anglais'], 'currency' => 'Dollar américain', 'preposition' => 'plural'],
            ['name' => 'France', 'languages' => ['Français'], 'currency' => 'Euro', 'preposition' => 'female'],
            ['name' => 'Espagne', 'languages' => ['Espagnol'], 'currency' => 'Euro', 'preposition' => 'female'],
            ['name' => 'Allemagne', 'languages' => ['Allemand'], 'currency' => 'Euro', 'preposition' => 'female'],
            ['name' => 'Italie', 'languages' => ['Italien'], 'currency' => 'Euro', 'preposition' => 'female'],
            ['name' => 'Portugal', 'languages' => ['Portugais'], 'currency' => 'Euro', 'preposition' => 'male'],
            ['name' => 'Russie', 'languages' => ['Russe'], 'currency' => 'Rouble russe', 'preposition' => 'female'],
            ['name' => 'Chine', 'languages' => ['Chinois'], 'currency' => 'Yuan chinois', 'preposition' => 'female'],
            ['name' => 'Japon', 'languages' => ['Japonais'], 'currency' => 'Yen japonais', 'preposition' => 'male'],
            ['name' => 'Arabie Saoudite', 'languages' => ['Arabe'], 'currency' => 'Riyal saoudien', 'preposition' => 'female'],
            ['name' => 'Corée du Sud', 'languages' => ['Coréen'], 'currency' => 'Won sud-coréen', 'preposition' => 'female'],
            ['name' => 'Inde', 'languages' => ['Hindî', 'Anglais'], 'currency' => 'Roupie indienne', 'preposition' => 'female'],
            ['name' => 'Bangladesh', 'languages' => ['Bengali'], 'currency' => 'Taka bangladais', 'preposition' => 'male'],
            ['name' => 'Pakistan', 'languages' => ['Panjabi'], 'currency' => 'Roupie pakistanaise', 'preposition' => 'male'],
            ['name' => 'Indonésie', 'languages' => ['Javanais'], 'currency' => 'Roupie indonésienne', 'preposition' => 'female'],
            ['name' => 'Brésil', 'languages' => ['Portugais'], 'currency' => 'Real brésilien', 'preposition' => 'male'],
            ['name' => 'Vietnam', 'languages' => ['Vietnamien'], 'currency' => 'Dong vietnamien', 'preposition' => 'male'],
            ['name' => 'Turquie', 'languages' => ['Turc'], 'currency' => 'Lira turque', 'preposition' => 'female'],
            ['name' => 'Mexique', 'languages' => ['Espagnol'], 'currency' => 'Peso mexicain', 'preposition' => 'male'],
            ['name' => 'Canada', 'languages' => ['Anglais', 'Français'], 'currency' => 'Dollar canadien', 'preposition' => 'male'],
            ['name' => 'Australie', 'languages' => ['Anglais'], 'currency' => 'Dollar australien', 'preposition' => 'female'],
            ['name' => 'Argentine', 'languages' => ['Espagnol'], 'currency' => 'Peso argentin', 'preposition' => 'female'],
            ['name' => 'Nigéria', 'languages' => ['Anglais'], 'currency' => 'Naira nigérian', 'preposition' => 'male'],
            ['name' => 'Égypte', 'languages' => ['Arabe'], 'currency' => 'Livre égyptienne', 'preposition' => 'female'],
            ['name' => 'Suisse', 'languages' => ['Français', 'Allemand', 'Italien'], 'currency' => 'Franc suisse', 'preposition' => 'female'],
        ];

        $this->createUsers($manager, $this->passwordHasher, $faker);
        $this->createCountries($manager, $countries, $faker);
        $this->createActivities($manager, $faker);
        $this->createTravels($manager, $faker);
        $this->createComments($manager, $faker);
    }

    protected function createUsers(ObjectManager $manager, UserPasswordHasherInterface $passwordHasher, $faker): void
    {
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setEmail("test_$i@example.com");
            $user->setName($faker->firstName());
            $user->setFamilyName($faker->lastName());

            $password = "test";
            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            // First user is super admin, second normal admin, last user is banned
            if ($i === 0) {
                $user->setRoles(["ROLE_SUPER_ADMIN"]);
            } else if ($i === 1){
                $user->setRoles(["ROLE_ADMIN"]);
            }else if ($i === 19) { 
                $user->setRoles(["ROLE_BANNED", "ROLE_USER"]);
            } else{
                $user->setRoles(["ROLE_USER"]);
            }

            $manager->persist($user);
        }

        $manager->flush();
    }

    protected function createCountries(ObjectManager $manager, array $countries, $faker): void
    {
        foreach ($countries as $element) {
            $country = new Country();
            $country->setName($element['name'])->setPreposition($element['preposition']);

            // Create or fetch the currency
            $currency = $this->findOrCreateCurrency($manager, $element['currency']);
            $country->setCurrency($currency);

            // Create or fetch languages
            foreach ($element['languages'] as $languageName) {
                $language = $this->findOrCreateLanguage($manager, $languageName);
                $country->addLanguage($language);
            }
            $manager->persist($country);
        }

        $manager->flush();
    }

    protected function createActivities(ObjectManager $manager, $faker): void
    {
        $countries = $manager->getRepository(Country::class)->findAll();

        foreach ($countries as $country) {
            $activityCount = rand(1, 5);
            for ($i = 0; $i < $activityCount; $i++) {
                $activity = new Activity();
                $activity->setName($faker->sentence(3))
                         ->setDescription($faker->paragraph(10))
                         ->setLocation($faker->words(4, true))
                         ->setCountry($country);
                $manager->persist($activity);
            }
        }

        $manager->flush();
    }

    protected function createTravels(ObjectManager $manager, $faker): void
    {
        $countries = $manager->getRepository(Country::class)->findAll();

        foreach ($countries as $country) {
            $travelCount = rand(0, 3);
            for ($i = 0; $i < $travelCount; $i++) {
                $startAt = new \DateTimeImmutable($faker->dateTimeThisYear()->format('Y-m-d'));
                $endAt = $startAt->modify('+' . rand(1, 30) . ' days');


                $travel = new Travel();
                $travel->setDestination($country)
                       ->setStartAt($startAt)
                       ->setEndAt($endAt)
                       ->setDescription($faker->paragraphs(3, true))
                       ->setPrice($faker->randomFloat(2, 100, 10000))
                       ->setAvailableSpots(rand(1, 50));
                $manager->persist($travel);
            }
        }

        $manager->flush();
    }

    protected function findOrCreateLanguage(ObjectManager $manager, string $languageName): Language
    {
        $language = $manager->getRepository(Language::class)->findOneBy(['name' => $languageName]);

        if (!$language) {
            $language = new Language();
            $language->setName($languageName);
            $manager->persist($language);
        }

        return $language;
    }

    protected function findOrCreateCurrency(ObjectManager $manager, string $currencyName): Currency
    {
        $currency = $manager->getRepository(Currency::class)->findOneBy(['name' => $currencyName]);

        if (!$currency) {
            $currency = new Currency();
            $currency->setName($currencyName);
            $manager->persist($currency);
        }

        return $currency;
    }

    protected function createComments(ObjectManager $manager, $faker): void
    {
        $users = $manager->getRepository(User::class)->findAll();
        $travels = $manager->getRepository(Travel::class)->findAll();

        foreach ($travels as $travel) {
            $commentCount = rand(0, 25);
            for ($i = 0; $i < $commentCount; $i++) {
                $comment = new Comment();
                $comment->setContent($faker->paragraph())
                        ->setAuthor($faker->randomElement($users))
                        ->setPostedAt(new \DateTimeImmutable($faker->dateTimeThisYear()->format('Y-m-d H:i:s')))
                        ->setTravel($travel);

                $manager->persist($comment);
            }
        }

        $manager->flush();
    }
}
