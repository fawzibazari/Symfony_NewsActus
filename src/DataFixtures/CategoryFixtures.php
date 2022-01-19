<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoryFixtures extends Fixture
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    
    public function load(ObjectManager $manager): void
    {
        $categories = [
            'Politique',
            'Société',
            'Sport',
            'Cinéma',
            'Santé',
            'Mode',
            'Sciences',
            'Environnement',
        ];

        foreach($categories as $cat){
            $category = new Category();
            $category->setName($cat);
            $category->setAlias($this->slugger->slug($cat));

            $manager->persist($category);

        }
        $manager->flush();
    }
}
