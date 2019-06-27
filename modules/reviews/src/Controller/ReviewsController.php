<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\reviews\Controller;


use Pyshnov\Core\Controller\BaseController;
use Pyshnov\reviews\Model\ReviewsModel;

class ReviewsController extends BaseController {

    /**
     * Вывод отзывов в клиенткой части
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function reviews()
    {
        $title = $this->config()->get('reviews.title');

        $this->template()->setTitle($title);
        $this->template()->setMetaTitle($this->config()->get('reviews.meta_title') ?: $title);
        $this->template()->setMetaDescription($this->config()->get('reviews.description'));
        $this->template()->setMetaKeywords($this->config()->get('reviews.keywords'));

        $this->breadcrumb([
            $this->t('system.main') => '/',
            $title
        ]);

        $model = new ReviewsModel();
        $model->setContainer($this->container);

        $data['reviews'] = $model->getReviews();

        return $this->render($data, 'reviews');
    }

    public function adminReviews()
    {
        $title = $this->t('reviews.name');

        $this->template()->setTitle($title);
        $this->template()->setMetaTitle($this->t('system.control_panel') . ' | ' . $title);

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            $title
        ]);

        $model = new ReviewsModel();
        $model->setContainer($this->getContainer());
        $data['page'] = $model->getReviewsAll();

        return $this->render($data);
    }

    public function adminReviewEdit()
    {
        $title = $this->t('reviews.edit');

        $this->template()->setTitle($title);
        $this->template()->setMetaTitle($this->t('system.control_panel') . ' | ' . $title);

        $this->breadcrumb([
            $this->t('system.main') => '/admin/',
            $this->t('reviews.name') => '/admin/reviews/',
            $title
        ]);

        $model = new ReviewsModel();
        $model->setContainer($this->getContainer());

        if($this->request()->request->get('do') == 'edit' && $model->update()) {
            header("Location: /admin/reviews/");
            exit();
        }

        $data['review'] = $model->getReviewById();

        return $this->render($data);
    }

}