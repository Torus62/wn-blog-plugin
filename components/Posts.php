<?php namespace Torus\Blog\Components;

use Illuminate\Pagination\AbstractPaginator;
use Lang;
use Redirect;
use BackendAuth;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Winter\Storm\Database\Model;
use Winter\Storm\Database\Collection;
use Torus\Blog\Models\Post as BlogPost;
use Torus\Blog\Models\Category as BlogCategory;
use Torus\Blog\Models\Settings as BlogSettings;

class Posts extends ComponentBase
{
    /**
     * A collection of posts to display
     *
     * @var Collection|AbstractPaginator|null
     */
    public Collection|AbstractPaginator|null $posts;

    /**
     * Parameter to use for the page number
     *
     * @var string|null
     */
    public ?string $pageParam;

    /**
     * If the post list should be filtered by a category, the model to use
     *
     * @var Model|null
     */
    public ?Model $category;

    /**
     * Message to display when there are no messages
     *
     * @var string|null
     */
    public ?string $noPostsMessage;

    /**
     * Reference to the page name for linking to posts
     *
     * @var string|null
     */
    public ?string $postPage;

    /**
     * Reference to the page name for linking to categories
     *
     * @var string|null
     */
    public ?string $categoryPage;

    /**
     * If the post list should be ordered by another attribute
     *
     * @var string|null
     */
    public ?string $sortOrder;

    /**
     * @return string[]
     */
    public function componentDetails()
    {
        return [
            'name'        => 'winter.blog::lang.settings.posts_title',
            'description' => 'winter.blog::lang.settings.posts_description'
        ];
    }

    /**
     * @return array
     */
    public function defineProperties()
    {
        return [
            'pageNumber' => [
                'title'       => 'winter.blog::lang.settings.posts_pagination',
                'description' => 'winter.blog::lang.settings.posts_pagination_description',
                'type'        => 'string',
                'default'     => '{{ :page }}',
            ],
            'categoryFilter' => [
                'title'       => 'winter.blog::lang.settings.posts_filter',
                'description' => 'winter.blog::lang.settings.posts_filter_description',
                'type'        => 'string',
                'default'     => '',
            ],
            'categoriesFilter' => [
                'title'             => 'Filter on categories',
                'description'       => 'A comma-seperated list of categories to show posts for',
                'type'              => 'string',
                'default'           => ''
            ],
            'postsPerPage' => [
                'title'             => 'winter.blog::lang.settings.posts_per_page',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'winter.blog::lang.settings.posts_per_page_validation',
                'default'           => '10',
            ],
            'noPostsMessage' => [
                'title'             => 'winter.blog::lang.settings.posts_no_posts',
                'description'       => 'winter.blog::lang.settings.posts_no_posts_description',
                'type'              => 'string',
                'default'           => Lang::get('winter.blog::lang.settings.posts_no_posts_default'),
                'showExternalParam' => false,
            ],
            'sortOrder' => [
                'title'       => 'winter.blog::lang.settings.posts_order',
                'description' => 'winter.blog::lang.settings.posts_order_description',
                'type'        => 'dropdown',
                'default'     => 'published_at desc',
            ],
            'categoryPage' => [
                'title'       => 'winter.blog::lang.settings.posts_category',
                'description' => 'winter.blog::lang.settings.posts_category_description',
                'type'        => 'dropdown',
                'default'     => 'blog/category',
                'group'       => 'winter.blog::lang.settings.group_links',
            ],
            'postPage' => [
                'title'       => 'winter.blog::lang.settings.posts_post',
                'description' => 'winter.blog::lang.settings.posts_post_description',
                'type'        => 'dropdown',
                'default'     => 'blog/post',
                'group'       => 'winter.blog::lang.settings.group_links',
            ],
            'exceptPost' => [
                'title'             => 'winter.blog::lang.settings.posts_except_post',
                'description'       => 'winter.blog::lang.settings.posts_except_post_description',
                'type'              => 'string',
                'validationPattern' => '^[a-z0-9\-_,\s]+$',
                'validationMessage' => 'winter.blog::lang.settings.posts_except_post_validation',
                'default'           => '',
                'group'             => 'winter.blog::lang.settings.group_exceptions',
            ],
            'exceptCategories' => [
                'title'             => 'winter.blog::lang.settings.posts_except_categories',
                'description'       => 'winter.blog::lang.settings.posts_except_categories_description',
                'type'              => 'string',
                'validationPattern' => '^[a-z0-9\-_,\s]+$',
                'validationMessage' => 'winter.blog::lang.settings.posts_except_categories_validation',
                'default'           => '',
                'group'             => 'winter.blog::lang.settings.group_exceptions',
            ],
            'limit' => [
                'title'             => 'Post limit',
                'description'       => 'The total number of posts to return',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Must be a whole number',
                'default'           => ''
            ],
            'month' => [
                'title'             => 'Post month',
                'description'       => 'The month to return posts for',
                'type'              => 'string',
                'validationPattern' => '^[1-12]+$',
                'validationMessage' => 'Must be a whole number from 1 to 12',
                'default'           => ''
            ],
            'year' => [
                'title'             => 'Post year',
                'description'       => 'The year to return posts for',
                'type'              => 'string',
                'validationPattern' => '^[2010-2100]+$',
                'validationMessage' => 'Must be a whole number between 2010 and 2100',
                'default'           => ''
            ]
        ];
    }

    public function getCategoryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getPostPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getSortOrderOptions()
    {
        $options = BlogPost::$allowedSortingOptions;

        foreach ($options as $key => $value) {
            $options[$key] = Lang::get($value);
        }

        return $options;
    }

    public function onRun()
    {
        $this->prepareVars();

        $this->category = $this->page['category'] = $this->loadCategory();
        $this->posts = $this->page['posts'] = $this->listPosts();

        /*
         * If the page number is not valid, redirect
         */
        if ($pageNumberParam = $this->paramName('pageNumber')) {
            $currentPage = $this->property('pageNumber');

            if ($currentPage > ($lastPage = $this->posts->lastPage()) && $currentPage > 1) {
                return Redirect::to($this->currentPageUrl([$pageNumberParam => $lastPage]));
            }
        }
    }

    protected function prepareVars()
    {
        $this->pageParam = $this->page['pageParam'] = $this->paramName('pageNumber');
        $this->noPostsMessage = $this->page['noPostsMessage'] = $this->property('noPostsMessage');

        /*
         * Page links
         */
        $this->postPage = $this->page['postPage'] = $this->property('postPage');
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
    }

    protected function listPosts()
    {
        $category = $this->category ? $this->category->id : null;
        $categorySlug = $this->category ? $this->category->slug : null;

        /*
         * List all the posts, eager load their categories
         */
        $isPublished = !$this->checkEditor();

        $posts = BlogPost::with(['categories', 'featured_images'])->listFrontEnd([
            'page'             => $this->property('pageNumber'),
            'sort'             => $this->property('sortOrder'),
            'perPage'          => $this->property('postsPerPage'),
            'search'           => trim(input('search')),
            'category'         => $category,
            'categories'       => is_array($this->property('categoriesFilter'))
                ? $this->property('categoriesFilter')
                : preg_split('/,\s*/', $this->property('categoriesFilter'), -1, PREG_SPLIT_NO_EMPTY),
            'limit'            => $this->property('limit'),
            'month'             => $this->property('month'),
            'year'             => $this->property('year'),
            'published'        => $isPublished,
            'exceptPost'       => is_array($this->property('exceptPost'))
                ? $this->property('exceptPost')
                : preg_split('/,\s*/', $this->property('exceptPost'), -1, PREG_SPLIT_NO_EMPTY),
            'exceptCategories' => is_array($this->property('exceptCategories'))
                ? $this->property('exceptCategories')
                : preg_split('/,\s*/', $this->property('exceptCategories'), -1, PREG_SPLIT_NO_EMPTY),
        ]);

        /*
         * Add a "url" helper attribute for linking to each post and category
         */
        $posts->each(function ($post) use ($categorySlug) {
            $post->setUrl($this->postPage, $this->controller, ['category' => $categorySlug]);

            $post->categories->each(function ($category) {
                $category->setUrl($this->categoryPage, $this->controller);
            });
        });

        return $posts;
    }

    protected function loadCategory()
    {
        if (!$slug = $this->property('categoryFilter')) {
            return null;
        }

        $category = new BlogCategory;

        $category = $category->isClassExtendedWith('Winter.Translate.Behaviors.TranslatableModel')
            ? $category->transWhere('slug', $slug)
            : $category->where('slug', $slug);

        $category = $category->first();

        return $category ?: null;
    }

    protected function checkEditor()
    {
        $backendUser = BackendAuth::getUser();

        return $backendUser
            && $backendUser->hasAccess('winter.blog.access_posts')
            && BlogSettings::get('show_all_posts', true);
    }
}
