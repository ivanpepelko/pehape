<?php

namespace App\Controller;

use App\Entity\Gallery;
use App\Entity\Image;
use App\Form\GalleryType;
use App\Form\ImageType;
use App\Repository\GalleryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/gallery')]
#[IsGranted('ROLE_ADMIN')]
class GalleryController extends AbstractController
{
    #[Route('/', name: 'gallery_index', methods: ['GET'])]
    public function index(
        GalleryRepository $galleryRepository
    ): Response {
        return $this->render(
            'gallery/index.html.twig',
            [
                'galleries' => $galleryRepository->findAll(),
            ]
        );
    }

    #[Route('/new', name: 'gallery_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request
    ): Response {
        $gallery = new Gallery();
        $form = $this->createForm(GalleryType::class, $gallery);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($gallery);
            $entityManager->flush();

            return $this->redirectToRoute('gallery_index');
        }

        return $this->render(
            'gallery/new.html.twig',
            [
                'gallery' => $gallery,
                'form'    => $form->createView(),
            ]
        );
    }

    #[Route('/{id}/edit', name: 'gallery_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Gallery $gallery,
        EntityManagerInterface $entityManager,
        MimeTypesInterface $mimeTypes
    ): Response {
        $form = $this->createForm(GalleryType::class, $gallery);
        $form->handleRequest($request);

        $image = new Image();
        $imageUploadForm = $this->createForm(ImageType::class, $image);
        $imageUploadForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('gallery_index');
        }

        if ($imageUploadForm->isSubmitted() && $imageUploadForm->isValid()) {
            /** @var UploadedFile $uploadedImage */
            $uploadedImage = $imageUploadForm->get('path')->getData();
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/img/upload';

            $extension = $mimeTypes->getExtensions($uploadedImage->getMimeType())[0] ?? 'error';
            $file = $uploadedImage->move($uploadDir, uniqid('image', true) . ".$extension");

            $gallery->addImage($image->setPath("/img/upload/{$file->getFilename()}"));
            $entityManager->flush();

            return $this->redirectToRoute('gallery_edit', ['id' => $gallery->getId()]);
        }

        return $this->render(
            'gallery/edit.html.twig',
            [
                'gallery'         => $gallery,
                'form'            => $form->createView(),
                'imageUploadForm' => $imageUploadForm->createView(),
            ]
        );
    }

    #[Route('/{id}', name: 'gallery_delete', methods: ['DELETE'])]
    public function delete(
        Request $request,
        Gallery $gallery,
        EntityManagerInterface $entityManager,
        Filesystem $filesystem
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $gallery->getId(), $request->request->get('_token'))) {
            $publicDir = $this->getParameter('kernel.project_dir') . '/public';
            $filesystem->remove($gallery->getImages()->map(fn(Image $i) => $publicDir . $i->getPath()));
            $entityManager->remove($gallery);
            $entityManager->flush();
        }

        return $this->redirectToRoute('gallery_index');
    }
}
