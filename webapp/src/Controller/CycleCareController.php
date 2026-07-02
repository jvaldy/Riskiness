<?php

namespace App\Controller;

use App\Entity\CycleCareEvent;
use App\Entity\CycleCarePeriod;
use App\Entity\User;
use App\Repository\CycleCareEventRepository;
use App\Repository\CycleCarePeriodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CycleCareController extends AbstractController
{
    private const EVENT_TYPES = [
        'period' => ['label' => 'Regles', 'icon' => 'drop'],
        'intimacy' => ['label' => 'Rapport', 'icon' => 'eggplant'],
        'protected' => ['label' => 'Rapport protege', 'icon' => 'shield'],
        'note' => ['label' => 'Note', 'icon' => 'note'],
        'test' => ['label' => 'Test grossesse', 'icon' => 'baby'],
        'contraception' => ['label' => 'Contraception', 'icon' => 'pill'],
    ];

    private const PHASES = [
        ['key' => 'period', 'label' => 'Regles', 'icon' => 'drop'],
        ['key' => 'follicular', 'label' => 'Phase folliculaire', 'icon' => 'leaf'],
        ['key' => 'fertile', 'label' => 'Fenetre fertile', 'icon' => 'sprout'],
        ['key' => 'ovulation', 'label' => 'Ovulation estimee', 'icon' => 'egg'],
        ['key' => 'luteal', 'label' => 'Phase luteale', 'icon' => 'moon'],
        ['key' => 'premenstrual', 'label' => 'Phase premenstruelle', 'icon' => 'cloud'],
    ];

    #[Route('/cycle-care', name: 'app_cycle_care', methods: ['GET'])]
    public function index(): Response
    {
        if (!$this->getUser() instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('cycle_care/index.html.twig', [
            'eventTypes' => self::EVENT_TYPES,
            'phases' => self::PHASES,
        ]);
    }

    #[Route('/cycle-care/data', name: 'app_cycle_care_data', methods: ['GET'])]
    public function data(CycleCarePeriodRepository $periods, CycleCareEventRepository $events): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        $periodList = $periods->findByUserOrdered($user);
        $eventList = $events->findByUserOrdered($user);

        return $this->json([
            'periods' => array_map(fn (CycleCarePeriod $period): array => $this->serializePeriod($period), $periodList),
            'events' => array_map(fn (CycleCareEvent $event): array => $this->serializeEvent($event), $eventList),
            'insights' => $this->buildInsights($periodList),
            'eventTypes' => self::EVENT_TYPES,
            'phases' => self::PHASES,
        ]);
    }

    #[Route('/cycle-care/periods', name: 'app_cycle_care_period_save', methods: ['POST'])]
    public function savePeriod(
        Request $request,
        EntityManagerInterface $entityManager,
        CycleCarePeriodRepository $periods
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->isCsrfTokenValid('cycle_care', (string) $request->headers->get('X-CSRF-Token'))) {
            return $this->json(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON payload.'], Response::HTTP_BAD_REQUEST);
        }

        $startDate = $this->parseDate((string) ($payload['startDate'] ?? ''));
        $endDate = $this->parseDate((string) ($payload['endDate'] ?? ''));
        if (!$startDate instanceof \DateTimeImmutable || !$endDate instanceof \DateTimeImmutable) {
            return $this->json(['error' => 'Les dates de debut et de fin sont obligatoires.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($endDate < $startDate) {
            return $this->json(['error' => 'La date de fin doit etre apres la date de debut.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $periodId = (int) ($payload['id'] ?? 0);
        $period = $periodId > 0 ? $periods->findOneByUserAndId($user, $periodId) : null;
        if ($periodId > 0 && !$period instanceof CycleCarePeriod) {
            return $this->json(['error' => 'Periode introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $period ??= new CycleCarePeriod($user);
        $period
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setNote($this->cleanText($payload['note'] ?? '', 800));

        $entityManager->persist($period);
        $entityManager->flush();

        return $this->json(['period' => $this->serializePeriod($period)]);
    }

    #[Route('/cycle-care/periods/{id}', name: 'app_cycle_care_period_delete', methods: ['DELETE'])]
    public function deletePeriod(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        CycleCarePeriodRepository $periods
    ): JsonResponse {
        $period = $this->guardedPeriod($id, $request, $periods);
        if ($period instanceof JsonResponse) {
            return $period;
        }

        $entityManager->remove($period);
        $entityManager->flush();

        return $this->json(['deleted' => true]);
    }

    #[Route('/cycle-care/events', name: 'app_cycle_care_event_save', methods: ['POST'])]
    public function saveEvent(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->isCsrfTokenValid('cycle_care', (string) $request->headers->get('X-CSRF-Token'))) {
            return $this->json(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON payload.'], Response::HTTP_BAD_REQUEST);
        }

        $eventDate = $this->parseDate((string) ($payload['eventDate'] ?? ''));
        if (!$eventDate instanceof \DateTimeImmutable) {
            return $this->json(['error' => 'La date est obligatoire.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $event = new CycleCareEvent($user);
        $event
            ->setEventDate($eventDate)
            ->setType($this->allowedEventType((string) ($payload['type'] ?? 'note')))
            ->setNote($this->cleanText($payload['note'] ?? '', 500));

        $entityManager->persist($event);
        $entityManager->flush();

        return $this->json(['event' => $this->serializeEvent($event)]);
    }

    #[Route('/cycle-care/events/{id}', name: 'app_cycle_care_event_delete', methods: ['DELETE'])]
    public function deleteEvent(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        CycleCareEventRepository $events
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->isCsrfTokenValid('cycle_care', (string) $request->headers->get('X-CSRF-Token'))) {
            return $this->json(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        $event = $events->findOneByUserAndId($user, $id);
        if (!$event instanceof CycleCareEvent) {
            return $this->json(['error' => 'Evenement introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($event);
        $entityManager->flush();

        return $this->json(['deleted' => true]);
    }

    private function guardedPeriod(
        int $id,
        Request $request,
        CycleCarePeriodRepository $periods
    ): CycleCarePeriod|JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->isCsrfTokenValid('cycle_care', (string) $request->headers->get('X-CSRF-Token'))) {
            return $this->json(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        $period = $periods->findOneByUserAndId($user, $id);
        if (!$period instanceof CycleCarePeriod) {
            return $this->json(['error' => 'Periode introuvable.'], Response::HTTP_NOT_FOUND);
        }

        return $period;
    }

    /**
     * @param CycleCarePeriod[] $periods
     * @return array<string, mixed>
     */
    private function buildInsights(array $periods): array
    {
        $ascending = array_reverse($periods);
        $periodCount = count($ascending);
        $periodDurations = [];
        $cycleDurations = [];

        foreach ($ascending as $index => $period) {
            $duration = $this->daysBetween($period->getStartDate(), $period->getEndDate()) + 1;
            $periodDurations[] = max(1, min(14, $duration));
            if ($index > 0) {
                $cycleLength = $this->daysBetween($ascending[$index - 1]->getStartDate(), $period->getStartDate());
                if ($cycleLength >= 15 && $cycleLength <= 90) {
                    $cycleDurations[] = $cycleLength;
                }
            }
        }

        $averagePeriod = max(1, (int) round($this->robustAverage($periodDurations, 8) ?: 5));
        $averageCycle = max(15, (int) round($this->robustAverage($cycleDurations, 12) ?: 28));
        $cycleProfile = $this->cycleProfile($cycleDurations, $averageCycle);
        $predictionWindow = $this->predictionWindow($cycleDurations);
        $today = new \DateTimeImmutable('today');
        $lastPeriod = $ascending[$periodCount - 1] ?? null;
        $lastStart = $lastPeriod?->getStartDate();
        $nextPeriod = $lastStart?->modify('+' . $averageCycle . ' days');
        while ($nextPeriod instanceof \DateTimeImmutable && $nextPeriod < $today) {
            $nextPeriod = $nextPeriod->modify('+' . $averageCycle . ' days');
        }

        $ovulation = $nextPeriod?->modify('-14 days');
        $fertileStart = $ovulation?->modify('-5 days');
        $fertileEnd = $ovulation?->modify('+1 day');
        $cycleDay = $lastStart instanceof \DateTimeImmutable ? max(1, $this->daysBetween($lastStart, $today) + 1) : null;
        $progress = null !== $cycleDay ? min(100, (int) round(($cycleDay / $averageCycle) * 100)) : 0;
        $phase = $this->phaseFor($cycleDay, $averageCycle, $averagePeriod);

        return [
            'periodCount' => $periodCount,
            'averagePeriod' => $averagePeriod,
            'averageCycle' => $averageCycle,
            'cycleProfile' => $cycleProfile,
            'cycleDay' => $cycleDay,
            'cycleProgress' => $progress,
            'currentPhase' => $phase,
            'nextPeriodDate' => $nextPeriod?->format('Y-m-d') ?? '',
            'nextOvulationDate' => $ovulation?->format('Y-m-d') ?? '',
            'fertileStartDate' => $fertileStart?->format('Y-m-d') ?? '',
            'fertileEndDate' => $fertileEnd?->format('Y-m-d') ?? '',
            'reliability' => $this->reliability($periodCount, $cycleProfile),
            'badges' => $this->badges($periodCount, $cycleDurations),
            'goals' => $this->goals($periodCount),
            'timeline' => $this->timeline($averageCycle, $averagePeriod),
            'predictions' => $this->predictions($nextPeriod, $averageCycle, $averagePeriod, 6, $predictionWindow),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function phaseFor(?int $cycleDay, int $averageCycle, int $averagePeriod): array
    {
        if (null === $cycleDay) {
            return ['key' => 'unknown', 'label' => 'Premiere estimation', 'icon' => 'note'];
        }

        $ovulationDay = max($averagePeriod + 3, $averageCycle - 14);
        if ($cycleDay <= $averagePeriod) {
            return ['key' => 'period', 'label' => 'Regles', 'icon' => 'drop'];
        }
        if ($cycleDay >= $ovulationDay - 5 && $cycleDay <= $ovulationDay - 1) {
            return ['key' => 'fertile', 'label' => 'Fenetre fertile', 'icon' => 'baby'];
        }
        if ($cycleDay === $ovulationDay) {
            return ['key' => 'ovulation', 'label' => 'Ovulation estimee', 'icon' => 'egg'];
        }
        if ($cycleDay >= $averageCycle - 5) {
            return ['key' => 'premenstrual', 'label' => 'Phase premenstruelle', 'icon' => 'cloud'];
        }
        if ($cycleDay > $ovulationDay) {
            return ['key' => 'luteal', 'label' => 'Phase luteale', 'icon' => 'moon'];
        }

        return ['key' => 'follicular', 'label' => 'Phase folliculaire', 'icon' => 'leaf'];
    }

    /**
     * @return array<string, string>
     */
    /**
     * @param array<string, mixed> $cycleProfile
     * @return array<string, string>
     */
    private function reliability(int $periodCount, array $cycleProfile): array
    {
        if ($periodCount >= 3 && 'irregular' === $cycleProfile['regularity']) {
            return ['level' => 'variable', 'label' => 'Cycle irregulier', 'description' => 'Previsions sous forme de plage'];
        }

        if ($periodCount >= 3 && 'variable' === $cycleProfile['regularity']) {
            return ['level' => 'medium', 'label' => 'Cycle variable', 'description' => 'Previsions a surveiller'];
        }

        if ($periodCount >= 6) {
            return ['level' => 'good', 'label' => 'Historique solide', 'description' => 'Bonne base de prevision'];
        }

        if ($periodCount >= 3) {
            return ['level' => 'medium', 'label' => 'Tendance detectee', 'description' => 'Previsions en amelioration'];
        }

        return ['level' => 'low', 'label' => 'Premiere estimation', 'description' => 'Ajoute plus de cycles pour affiner'];
    }

    /**
     * @param int[] $cycleDurations
     * @return array<string, mixed>
     */
    private function cycleProfile(array $cycleDurations, int $averageCycle): array
    {
        $count = count($cycleDurations);
        $spread = $count > 0 ? max($cycleDurations) - min($cycleDurations) : 0;
        $deviation = $this->standardDeviation($cycleDurations);
        $regularity = match (true) {
            $count < 2 => 'insufficient',
            $deviation >= 7.0 || $spread >= 12 => 'irregular',
            $deviation >= 4.0 || $spread >= 7 => 'variable',
            default => 'regular',
        };

        $length = match (true) {
            $averageCycle < 24 => 'short',
            $averageCycle > 35 => 'long',
            default => 'standard',
        };

        $label = match ($regularity) {
            'irregular' => 'Cycle irregulier',
            'variable' => 'Cycle variable',
            'regular' => 'Cycle regulier',
            default => 'Historique insuffisant',
        };

        if ('short' === $length) {
            $label .= ' court';
        } elseif ('long' === $length) {
            $label .= ' long';
        }

        return [
            'regularity' => $regularity,
            'length' => $length,
            'label' => $label,
            'spread' => $spread,
            'deviation' => round($deviation, 1),
            'sampleSize' => $count,
        ];
    }

    /**
     * @param int[] $cycleDurations
     * @return array<int, array{label: string, active: bool}>
     */
    private function badges(int $periodCount, array $cycleDurations): array
    {
        $regular = count($cycleDurations) >= 3 && (max($cycleDurations) - min($cycleDurations)) <= 4;

        return [
            ['label' => 'Premier cycle saisi', 'active' => $periodCount >= 1],
            ['label' => '3 cycles suivis', 'active' => $periodCount >= 3],
            ['label' => '6 cycles suivis', 'active' => $periodCount >= 6],
            ['label' => 'Prevision amelioree', 'active' => $periodCount >= 3],
            ['label' => 'Historique solide', 'active' => $periodCount >= 6],
            ['label' => 'Cycle regulier', 'active' => $regular],
        ];
    }

    /**
     * @return string[]
     */
    private function goals(int $periodCount): array
    {
        if (0 === $periodCount) {
            return ['Ajouter un premier cycle'];
        }

        if ($periodCount < 3) {
            return ['Ajouter 3 cycles pour ameliorer les previsions'];
        }

        if ($periodCount < 6) {
            return ['Ajouter 6 cycles pour stabiliser les estimations'];
        }

        return ['Continuer a garder un historique regulier'];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function timeline(int $averageCycle, int $averagePeriod): array
    {
        $ovulationDay = max($averagePeriod + 3, $averageCycle - 14);

        return [
            ['key' => 'period', 'label' => 'Regles', 'icon' => 'drop', 'from' => 1, 'to' => $averagePeriod],
            ['key' => 'follicular', 'label' => 'Phase folliculaire', 'icon' => 'calendar', 'from' => $averagePeriod + 1, 'to' => max($averagePeriod + 1, $ovulationDay - 6)],
            ['key' => 'fertile', 'label' => 'Fenetre fertile', 'icon' => 'baby', 'from' => $ovulationDay - 5, 'to' => $ovulationDay - 1],
            ['key' => 'ovulation', 'label' => 'Ovulation estimee', 'icon' => 'egg', 'from' => $ovulationDay, 'to' => $ovulationDay],
            ['key' => 'luteal', 'label' => 'Phase luteale', 'icon' => 'moon', 'from' => $ovulationDay + 1, 'to' => max($ovulationDay + 1, $averageCycle - 6)],
            ['key' => 'premenstrual', 'label' => 'Phase premenstruelle', 'icon' => 'cloud', 'from' => max(1, $averageCycle - 5), 'to' => $averageCycle],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function predictions(?\DateTimeImmutable $nextPeriod, int $averageCycle, int $averagePeriod, int $count, int $window): array
    {
        if (!$nextPeriod instanceof \DateTimeImmutable) {
            return [];
        }

        $predictions = [];
        $date = $nextPeriod;
        for ($i = 0; $i < $count; ++$i) {
            $end = $date->modify('+' . max(0, $averagePeriod - 1) . ' days');
            $ovulation = $date->modify('-14 days');
            $predictions[] = [
                'periodStart' => $date->format('Y-m-d'),
                'periodEnd' => $end->format('Y-m-d'),
                'periodStartMin' => $date->modify('-' . $window . ' days')->format('Y-m-d'),
                'periodStartMax' => $date->modify('+' . $window . ' days')->format('Y-m-d'),
                'ovulation' => $ovulation->format('Y-m-d'),
                'fertileStart' => $ovulation->modify('-5 days')->format('Y-m-d'),
                'fertileEnd' => $ovulation->modify('+1 day')->format('Y-m-d'),
            ];
            $date = $date->modify('+' . $averageCycle . ' days');
        }

        return $predictions;
    }

    /**
     * @param int[] $values
     */
    private function average(array $values): ?float
    {
        if ([] === $values) {
            return null;
        }

        return array_sum($values) / count($values);
    }

    /**
     * @param int[] $values
     */
    private function robustAverage(array $values, int $maxSamples): ?float
    {
        if ([] === $values) {
            return null;
        }

        $samples = array_slice($values, -$maxSamples);
        sort($samples);

        if (count($samples) >= 5) {
            array_shift($samples);
            array_pop($samples);
        }

        return $this->average($samples);
    }

    /**
     * @param int[] $values
     */
    private function standardDeviation(array $values): float
    {
        if (count($values) < 2) {
            return 0.0;
        }

        $average = $this->average($values) ?? 0.0;
        $variance = array_sum(array_map(static fn (int $value): float => ($value - $average) ** 2, $values)) / count($values);

        return sqrt($variance);
    }

    /**
     * @param int[] $cycleDurations
     */
    private function predictionWindow(array $cycleDurations): int
    {
        if (count($cycleDurations) < 2) {
            return 2;
        }

        $deviation = $this->standardDeviation(array_slice($cycleDurations, -12));

        return max(2, min(10, (int) ceil($deviation)));
    }

    private function daysBetween(\DateTimeImmutable $start, \DateTimeImmutable $end): int
    {
        return (int) $start->diff($end)->format('%r%a');
    }

    private function allowedEventType(string $value): string
    {
        return array_key_exists($value, self::EVENT_TYPES) ? $value : 'note';
    }

    private function parseDate(string $value): ?\DateTimeImmutable
    {
        $value = trim($value);
        if ('' === $value) {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date instanceof \DateTimeImmutable ? $date : null;
    }

    private function cleanText(mixed $value, int $limit): string
    {
        return mb_substr(trim((string) $value), 0, $limit);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePeriod(CycleCarePeriod $period): array
    {
        return [
            'id' => $period->getId(),
            'startDate' => $period->getStartDate()->format('Y-m-d'),
            'endDate' => $period->getEndDate()->format('Y-m-d'),
            'duration' => $this->daysBetween($period->getStartDate(), $period->getEndDate()) + 1,
            'note' => $period->getNote() ?? '',
            'createdAt' => $period->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $period->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeEvent(CycleCareEvent $event): array
    {
        $type = $event->getType();

        return [
            'id' => $event->getId(),
            'eventDate' => $event->getEventDate()->format('Y-m-d'),
            'type' => $type,
            'label' => self::EVENT_TYPES[$type]['label'] ?? self::EVENT_TYPES['note']['label'],
            'icon' => self::EVENT_TYPES[$type]['icon'] ?? self::EVENT_TYPES['note']['icon'],
            'note' => $event->getNote() ?? '',
            'createdAt' => $event->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
