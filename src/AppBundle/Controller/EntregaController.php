<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Entrega;
use AppBundle\Entity\Socio;
use AppBundle\Entity\Temporada;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Service\TemporadaActual;


class EntregaController extends Controller
{
    /**
     * @Route("/entregas/listar", name="entregas_listar")
     * @Route("/entregas/listar/temporada/{temporada}", name="entregas_listar_temporada")
     * @Security("is_granted('ROLE_ENCARGADO')")
     */
    public function listarTemporadaAction(Temporada $temporada = null)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        //Si no recibe ninguna temporada se obtendrá la actual
        if (null === $temporada) {
            //Creamos una instancia del servicio
            $temporadaActual = new TemporadaActual($em);
            $temporada = $temporadaActual->temporadaActualAction();
        }

        $entregas = $em->getRepository('AppBundle:Entrega')
            ->getEntregasTemporada($temporada);

        return $this->render('entrega/listarTemporada.html.twig', [
            'entregas' => $entregas,
            'temporada' => $temporada
        ]);
    }

    /**
     * @Route("/entregas/listar/socio/{socio}", name="entregas_listar_socio")
     * @Route("/entregas/listar/socio/{socio}/{temporada}", name="entregas_listar_socio_temporada")
     * @Security("is_granted('ROLE_ENCARGADO') or user.getNif() == socio.getUsuario().getNif()")")
     */
    public function listarPorSocioAction(Socio $socio, Temporada $temporada = null)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        //Si no recibe ninguna temporada se obtendrá la actual
        if (null === $temporada) {
            //Creamos una instancia del servicio
            $temporadaActual = new TemporadaActual($em);
            $temporada = $temporadaActual->temporadaActualAction();
        }

        $entregas = $em->getRepository('AppBundle:Entrega')
            ->getEntregasSocioTemporada($socio, $temporada);

        return $this->render('entrega/listarSocioTemporada.html.twig', [
            'entregas' => $entregas,
            'socio' => $socio,
            'temporada' => $temporada
        ]);
    }

    /**
     * @Route("/entregas/detalle/{entrega}/{socio}", name="entregas_detalle")
     * @Security("is_granted('ROLE_ENCARGADO') or user.getNif() == socio.getUsuario().getNif()")")
     */
    public function detalleAction(Entrega $entrega, Socio $socio)
    {
        /** @var EntityManager $em */
        $em=$this->getDoctrine()->getManager();
        $resultado = $em->getRepository('AppBundle:Entrega')
            ->getEntregasDetalle($entrega, $socio);

        return $this->render('entrega/detalle.html.twig', [
            'resultado' => $resultado,
            'socio' => $socio,
            'entrega' => $entrega
        ]);
    }

    /**
     * @Route("/entregas/insertar", name="entregas_insertar")
     * @Security("is_granted('ROLE_ENCARGADO')")
     */
    public function insertarAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        //Obtención de procedencias
        $procedencias = $em->getRepository('AppBundle:Procedencia')
            ->findAll();

        //Obtención de fincas
        $fincas = $em->getRepository('AppBundle:Finca')
            ->findAll();

        //Obtención de la temporada en vigor
        $temporadaActual = new TemporadaActual($em);
        $temporada = $temporadaActual->temporadaActualAction();

        //Obtención de los lotes esta temporada
        $lotes = $em->getRepository('AppBundle:Lote')
            ->getLotesTemporada($temporada);

        $entregas = [
            [0, "2017-03-28", "16:15", "16:20", 1500, 0.18, null, null, 1, $procedencias[0], null, $fincas[0], $temporada, $lotes[0]],
            [0, "2017-03-28", "16:20", "16:25", 500, 0.23, 0.15, "Muy sucia", 1, $procedencias[1], null, $fincas[0], $temporada, $lotes[1]],
            [0, "2017-03-28", "16:25", "16:30", 200, 0.25, null, null, 2, $procedencias[1], null, $fincas[2], $temporada, $lotes[1]],
            [0, "2017-03-28", "16:30", "17:03", 1000, 0.22, 0.15, "Atasco de tolva", 3, $procedencias[1], null, $fincas[1], $temporada, $lotes[1]],
            [0, "2017-03-28", "17:07", "17:20", 900, 0.18, null, null, 3, $procedencias[0], null, $fincas[2], $temporada, $lotes[0]]
        ];

        foreach ($entregas as $item) {
            $entrega = new Entrega();
            $em->persist($entrega);
            $entrega
                ->setFecha(new \DateTime($item[1]))
                ->setHoraInicio(new \DateTime($item[2]))
                ->setHoraFin(new \DateTime($item[3]))
                ->setPeso($item[4])
                ->setRendimiento($item[5])
                ->setSancion($item[6])
                ->setObservaciones($item[7])
                ->setBascula($item[8])
                ->setProcedencia($item[9])
                ->setFinca($item[11])
                ->setTemporada($item[12]);

            $em->flush();
        }
        $mensaje = 'Entradas insertadas correctamente';

        return $this->render('entrega/confirma.html.twig', [
            'mensaje' => $mensaje
        ]);
    }

    /**
     * @Route("/entregas/asignar/partida", name="entregas_asignar_partida")
     */
    public function asignarPartidaAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        //Obtención de procedencias
        $partidas = $em->getRepository('AppBundle:Partida')
            ->findAll();

        //Obtención de entregas
        $entregas = $em->getRepository('AppBundle:Entrega')
            ->findAll();

        $asignaciones = [
            $partidas[0],
            $partidas[1],
            $partidas[2],
            $partidas[2],
            $partidas[0],
        ];

        //array de sumas
        $sumas = [];

        //Asigna partida a cada entrega
        for ($i = 0; $i < sizeof($asignaciones); $i++) {
            $em->persist($entregas[$i]);
            $entregas[$i]
                ->setPartida($asignaciones[$i]);

            $em->flush();
        }

        //Rellena array de sumas
        foreach ($entregas as $item) {
            $suma = ($item->getPeso()) * ($item->getRendimiento());
            array_push($sumas, $suma);
        }

        //Suma cantidad a cada partida
        for ($i = 0; $i < sizeof($partidas); $i++) {
            $em->persist($partidas[$i]);
            $partidas[$i]
                ->setCantidad($sumas[$i]);

            $em->flush();
        }
        $mensaje = 'Partidas asignadas correctamente';

        return $this->render('entrega/confirma.html.twig', [
            'mensaje' => $mensaje
        ]);
    }
}
