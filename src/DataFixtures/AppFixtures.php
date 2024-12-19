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
            ['name' => 'United States', 'languages' => ['English'], 'currency' => 'US Dollar'],
            ['name' => 'France', 'languages' => ['French'], 'currency' => 'Euro'],
            ['name' => 'Spain', 'languages' => ['Spanish'], 'currency' => 'Euro'],
            ['name' => 'Germany', 'languages' => ['German'], 'currency' => 'Euro'],
            ['name' => 'Italy', 'languages' => ['Italian'], 'currency' => 'Euro'],
            ['name' => 'Portugal', 'languages' => ['Portuguese'], 'currency' => 'Euro'],
            ['name' => 'Russia', 'languages' => ['Russian'], 'currency' => 'Russian Ruble'],
            ['name' => 'China', 'languages' => ['Chinese'], 'currency' => 'Chinese Yuan'],
            ['name' => 'Japan', 'languages' => ['Japanese'], 'currency' => 'Japanese Yen'],
            ['name' => 'Saudi Arabia', 'languages' => ['Arabic'], 'currency' => 'Saudi Riyal'],
            ['name' => 'South Korea', 'languages' => ['Korean'], 'currency' => 'South Korean Won'],
            ['name' => 'India', 'languages' => ['Hindi', 'English'], 'currency' => 'Indian Rupee'],
            ['name' => 'Bangladesh', 'languages' => ['Bengali'], 'currency' => 'Bangladeshi Taka'],
            ['name' => 'Pakistan', 'languages' => ['Punjabi'], 'currency' => 'Pakistani Rupee'],
            ['name' => 'Indonesia', 'languages' => ['Javanese'], 'currency' => 'Indonesian Rupiah'],
            ['name' => 'Brazil', 'languages' => ['Portuguese'], 'currency' => 'Brazilian Real'],
            ['name' => 'Vietnam', 'languages' => ['Vietnamese'], 'currency' => 'Vietnamese Dong'],
            ['name' => 'Turkey', 'languages' => ['Turkish'], 'currency' => 'Turkish Lira'],
            ['name' => 'Mexico', 'languages' => ['Spanish'], 'currency' => 'Mexican Peso'],
            ['name' => 'Canada', 'languages' => ['English', 'French'], 'currency' => 'Canadian Dollar'],
            ['name' => 'Australia', 'languages' => ['English'], 'currency' => 'Australian Dollar'],
            ['name' => 'Argentina', 'languages' => ['Spanish'], 'currency' => 'Argentine Peso'],
            ['name' => 'Nigeria', 'languages' => ['English'], 'currency' => 'Nigerian Naira'],
            ['name' => 'Egypt', 'languages' => ['Arabic'], 'currency' => 'Egyptian Pound'],
            ['name' => 'Switzerland', 'languages' => ['French', 'German', 'Italian'], 'currency' => 'Swiss Franc'],
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

            // First Pierre is admin
            if ($i === 0) {
                $user->setRoles(["ROLE_ADMIN"]);
            } else {
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
            $country->setName($element['name']);

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
                       ->setPrice($faker->randomFloat(2, 100, 10000));
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
