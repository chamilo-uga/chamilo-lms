<?php

namespace Chamilo\CoreBundle\Controller;

use Chamilo\CoreBundle\Settings\SettingsManager;
use Chamilo\CoreBundle\Framework\Container;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    private $settingsManager;

    public function __construct(SettingsManager $settingsManager)
    {
        $this->settingsManager = $settingsManager;
    }

    #[Route('/home', name: 'home')]
    public function index(Request $request): Response
    {
        $manager = $this->settingsManager;
        $displayCategories = $manager->getParametersFromKeywordOrderedByCategory('display_categories_on_homepage')['display'][0]->getSelectedValue();
        $displayCategories = filter_var($displayCategories, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

        if ($displayCategories === true) {
            $courseCategoriesRepo = Container::getCourseCategoryRepository();
            $categories = $courseCategoriesRepo->findAll();
            $coursesWithCategory = [];
            foreach ($categories as $category) {
                $courses = [];
                foreach ($category->getCourses() as $course) {
                    $courses[] = [
                        'name' => $course->getTitle(),
                        'id' => $course->getId()
                    ];
                }
                $coursesWithCategory[] = [
                    'name' => $category->getTitle(),
                    'courses' => $courses
                ];
            }
        } else {
            $coursesWithCategory = [];
        }

        return $this->render('@ChamiloCore/Home/home.html.twig', [
            'displayCategories' => $displayCategories,
            'coursesWithCategory' => $coursesWithCategory,
            'title' => get_lang('Course categories'),
            'warningNoCategory' => get_lang('No courses category declared'),
            'warningNoCourseInCategory' => get_lang('No courses in category')
        ]);
    }

}
