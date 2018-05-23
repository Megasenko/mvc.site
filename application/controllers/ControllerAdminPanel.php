<?php

class ControllerAdminPanel extends Controller
{
    public function __construct()
    {
        $this->model = new ModelBlog();
        parent::__construct();
    }

    public function indexAction()
    {
        $this->view->generate('adminView.php', $data = null, $templateView = 'adminTemplateView.php');
    }

    /**
     * view all articles in table
     */
    public function articlesAction()
    {
        $data = $this->model->getArticles();
        $this->view->generate('adminArticlesView.php', $data, $templateView = 'adminTemplateView.php');

    }

    /**
     * Add article
     */
    public function addArticleAction()
    {
        $this->model->insertArticle();

        $this->view->generate('adminAddArticleView.php', $data=null, $templateView = 'adminTemplateView.php');

    }

    /** View article before update
     * @param $url
     */
    public function editArticleAction($url)
    {
        $data = $this->model->getArticle($url);
        $this->view->generate('adminUpdateArticleView.php', $data, $templateView = 'adminTemplateView.php');

    }

    /** Update article
     * @param $url
     */
    public function updateArticleAction($url)
    {
       if( $this->model->updateArticle($url)) {
           $data = $this->model->getArticles();
           $this->view->generate('adminArticlesView.php', $data, $templateView = 'adminTemplateView.php');
       }
    }

    /** Delete article
     * @param $url
     */
    public function delArticleAction($url)
    {
        $this->model->deleteArticle($url);
        $data = $this->model->getArticles();
        $this->view->generate('adminArticlesView.php', $data, $templateView = 'adminTemplateView.php');

    }

}
