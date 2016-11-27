<?php

namespace AppBundle\Facade;

use AppBundle\Entity\Category;
use AppBundle\Repository\CategoryRepository;

/**
 * @author Vašek Boch <vasek.boch@live.com>
 * @author Jan Klat <jenik@klatys.cz>
 */
class CategoryFacade
{

    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
	$this->categoryRepository = $categoryRepository;
    }

    /** @return Category */
    public function getBySlug($slug)
    {
	return $this->categoryRepository->findOneBy([
		    "slug" => $slug,
	]);
    }
    
    public function getById($id){
	return $this->categoryRepository->findOneBy([
	    "id" => $id,
	]);
    }

    /** @return Category[] */
    public function getParentCategories(Category $category)
    {
	return $this->categoryRepository->findBy(
			[
		    "parentCategory" => $category,
			], [
		    "rank" => "desc",
			]
	);
    }

    /** @return Category[] */
    public function getTopLevelCategories()
    {
	return $this->categoryRepository->findBy(
			[
		    "level" => 0,
			], [
		    "rank" => "desc",
			]
	);
    }

    /** @return Category[] */
    public function getTopLevelCategoriesWithLimit($limit)
    {
	return $this->categoryRepository->findBy(
			[
		    "level" => 0,
			], [
		    "rank" => "desc",
			], $limit, 0
	);
    }

    public function getChildCategoriesWithLimit($parentCategory, $limit)
    {
	return $this->categoryRepository->findBy(
			[
		    "parentCategory" => $parentCategory,
			], [
		    "rank" => "desc",
			], $limit, 0
	);
    }

}