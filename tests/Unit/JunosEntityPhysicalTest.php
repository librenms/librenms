<?php

namespace LibreNMS\Tests\Unit;

use App\Models\EntPhysical;
use Illuminate\Support\Collection;
use LibreNMS\OS\Junos;
use LibreNMS\Tests\TestCase;

final class JunosEntityPhysicalTest extends TestCase
{
    public function testDanglingOpticParentIsRepairedToThePic(): void
    {
        $entities = $this->mx([
            $this->entity(889, 'port', 71, 'QSFP-100GBASE-LR4 @ 1/0/2'),
        ]);

        $repaired = $this->parents(Junos::repairPortParents($entities, []));

        $this->assertSame(67, $repaired[889]);
    }

    public function testValidParentsAreUntouched(): void
    {
        $entities = $this->mx([
            $this->entity(119, 'port', 63, 'SFP+-10G-LR @ 0/0/0'),
            $this->entity(700, 'container', 67, 'Xcvr cage @ 1/0/3'),
            $this->entity(890, 'port', 700, 'SFP+-10G-LR @ 1/0/3'),
        ]);

        $repaired = $this->parents(Junos::repairPortParents($entities, []));

        $this->assertSame(63, $repaired[119]);
        $this->assertSame(700, $repaired[890]);
    }

    public function testOpticUnderThePicOfAnotherFpcIsRepaired(): void
    {
        $entities = $this->mx([
            $this->entity(23, 'container', 1, 'FPC: MPC 3D 16x 10GE @ 2/*/*'),
            $this->entity(71, 'module', 23, 'PIC: 4x 10GE(LAN) SFP+ @ 2/0/*'),
            $this->entity(889, 'port', 71, 'QSFP-100GBASE-LR4 @ 1/0/2'),
        ]);
        $entities->firstWhere('entPhysicalIndex', 889)->ifIndex = 900;

        $repaired = Junos::repairPortParents($entities, [71 => 900, 67 => 500])->keyBy('entPhysicalIndex');

        $this->assertSame(67, (int) $repaired[889]->entPhysicalContainedIn);
        $this->assertSame(500, $repaired[889]->ifIndex);
    }

    public function testDuplicatePicLocatorIsNotRepaired(): void
    {
        $entities = $this->mx([
            $this->entity(69, 'module', 22, 'PIC: DUPLICATE @ 1/0/*'),
            $this->entity(889, 'port', 71, 'QSFP-100GBASE-LR4 @ 1/0/2'),
        ]);

        $repaired = $this->parents(Junos::repairPortParents($entities, []));

        $this->assertSame(71, $repaired[889]);
    }

    public function testMalformedLocatorIsNotRepaired(): void
    {
        $entities = $this->mx([
            $this->entity(889, 'port', 71, 'QSFP-100GBASE-LR4 @ 1/0'),
            $this->entity(890, 'port', 71, 'QSFP-100GBASE-LR4 @ 1/0/3 spare'),
            $this->entity(891, 'port', 71, 'QSFP-100GBASE-LR4 @ 1/0/*'),
        ]);

        $repaired = $this->parents(Junos::repairPortParents($entities, []));

        $this->assertSame([71, 71, 71], [$repaired[889], $repaired[890], $repaired[891]]);
    }

    public function testPicOutsideAMatchingFpcDoesNotMakeTheLocatorAmbiguous(): void
    {
        $entities = $this->mx([
            $this->entity(69, 'module', 1, 'PIC: MRATE-6xQSFPP-XGE-XLGE-CGE @ 1/0/*'),
            $this->entity(889, 'port', 71, 'QSFP-100GBASE-LR4 @ 1/0/2'),
        ]);

        $repaired = $this->parents(Junos::repairPortParents($entities, []));

        $this->assertSame(67, $repaired[889]);
    }

    public function testPicOutsideAMatchingFpcIsNotACandidate(): void
    {
        $entities = new Collection([
            $this->entity(1, 'chassis', 0, 'Juniper MX480 Internet Backbone Router'),
            $this->entity(22, 'container', 1, 'FPC: MPC7E 3D MRATE-12xQSFPP-XGE-XLGE-CGE @ 2/*/*'),
            $this->entity(67, 'module', 22, 'PIC: MRATE-6xQSFPP-XGE-XLGE-CGE @ 1/0/*'),
            $this->entity(23, 'module', 1, 'FPC: MPC7E 3D MRATE-12xQSFPP-XGE-XLGE-CGE @ 1/*/*'),
            $this->entity(68, 'module', 23, 'PIC: MRATE-6xQSFPP-XGE-XLGE-CGE @ 1/1/*'),
            $this->entity(69, 'module', 1, 'PIC: MRATE-6xQSFPP-XGE-XLGE-CGE @ 1/2/*'),
            $this->entity(1079, 'port', 73, 'QSFP+-4X10G-SR @ 1/2/0'),
            $this->entity(889, 'port', 71, 'QSFP-100GBASE-LR4 @ 1/0/2'),
            $this->entity(984, 'port', 72, 'QSFP+-4X10G-SR @ 1/1/1'),
        ]);

        $repaired = $this->parents(Junos::repairPortParents($entities, []));

        $this->assertSame(71, $repaired[889]);
        $this->assertSame(72, $repaired[984]);
        $this->assertSame(73, $repaired[1079]);
    }

    public function testRepairThatWouldCreateACycleIsSkipped(): void
    {
        $entities = new Collection([
            $this->entity(22, 'container', 889, 'FPC: MPC7E 3D MRATE-12xQSFPP-XGE-XLGE-CGE @ 1/*/*'),
            $this->entity(67, 'module', 22, 'PIC: MRATE-6xQSFPP-XGE-XLGE-CGE @ 1/0/*'),
            $this->entity(889, 'port', 71, 'QSFP-100GBASE-LR4 @ 1/0/2'),
        ]);

        $repaired = $this->parents(Junos::repairPortParents($entities, []));

        $this->assertSame(71, $repaired[889]);
    }

    public function testOnlyPortsAreRepaired(): void
    {
        $entities = $this->mx([
            $this->entity(890, 'module', 71, 'QSFP-100GBASE-LR4 @ 1/0/3'),
        ]);

        $repaired = $this->parents(Junos::repairPortParents($entities, []));

        $this->assertSame(71, $repaired[890]);
    }

    public function testIfIndexFollowsTheRepairedParent(): void
    {
        $entities = $this->mx([
            $this->entity(889, 'port', 71, 'QSFP-100GBASE-LR4 @ 1/0/2'),
            $this->entity(984, 'port', 72, 'QSFP+-4X10G-SR @ 1/1/1'),
        ]);

        $repaired = Junos::repairPortParents($entities, [889 => 709, 67 => 500, 68 => 600])->keyBy('entPhysicalIndex');

        $this->assertSame(709, $repaired[889]->ifIndex);
        $this->assertSame(600, $repaired[984]->ifIndex);
    }

    /**
     * @param  EntPhysical[]  $extra
     * @return Collection<int, EntPhysical>
     */
    private function mx(array $extra): Collection
    {
        return new Collection(array_merge([
            $this->entity(1, 'chassis', 0, 'Juniper MX480 Internet Backbone Router'),
            $this->entity(21, 'container', 1, 'FPC: MPC 3D 16x 10GE @ 0/*/*'),
            $this->entity(22, 'container', 1, 'FPC: MPC7E 3D MRATE-12xQSFPP-XGE-XLGE-CGE @ 1/*/*'),
            $this->entity(63, 'module', 21, 'PIC: 4x 10GE(LAN) SFP+ @ 0/0/*'),
            $this->entity(67, 'module', 22, 'PIC: MRATE-6xQSFPP-XGE-XLGE-CGE @ 1/0/*'),
            $this->entity(68, 'module', 22, 'PIC: MRATE-6xQSFPP-XGE-XLGE-CGE @ 1/1/*'),
        ], $extra));
    }

    private function entity(int $index, string $class, int $parent, string $descr): EntPhysical
    {
        return new EntPhysical([
            'entPhysicalIndex' => $index,
            'entPhysicalClass' => $class,
            'entPhysicalContainedIn' => $parent,
            'entPhysicalDescr' => $descr,
        ]);
    }

    /**
     * @param  Collection<int, EntPhysical>  $entities
     * @return array<int, int>
     */
    private function parents(Collection $entities): array
    {
        return $entities->mapWithKeys(fn (EntPhysical $entity) => [$entity->entPhysicalIndex => (int) $entity->entPhysicalContainedIn])->all();
    }
}
