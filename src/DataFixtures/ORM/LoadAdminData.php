<?php

namespace App\DataFixtures\ORM;

use App\Admin\AdministratorFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadAdminData extends Fixture implements DependentFixtureInterface
{
    public function __construct(private readonly AdministratorFactory $administratorFactory)
    {
    }

    public function load(ObjectManager $manager)
    {
        $adminRoles = $this->getRoles([
            'ROLE_ADMIN_COMMUNICATION_MEDIAS',
            'ROLE_ADMIN_COMMUNICATION_HOME_BLOCKS',
            'ROLE_ADMIN_COMMUNICATION_LIVE_LINKS',
            'ROLE_ADMIN_ARCHIVES_PROPOSALS',
            'ROLE_ADMIN_ARCHIVES_EXPLICATIONS',
            'ROLE_ADMIN_ARCHIVES_FACEBOOK_PROFILES',
            'ROLE_ADMIN_COMMUNICATION_REDIRECTIONS',
            'ROLE_ADMIN_COMMUNICATION_NEWSLETTER_SUBSCRIPTIONS',
            'ROLE_ADMIN_ARCHIVES_JE_MARCHE',
            'ROLE_ADMIN_ADHERENT_UNREGISTRATIONS',
            'ROLE_ADMIN_ARCHIVES_TON_MACRON',
            'ROLE_ADMIN_ARCHIVES_MY_EUROPE',
            'ROLE_ADMIN_POLITIQUE_LEGISLATIVE_CANDIDATES',
            'ROLE_ADMIN_POLITIQUE_LEGISLATIVE_DISTRICT_ZONES',
            'ROLE_ADMIN_ADHERENT_ADHERENTS',
            'ROLE_ADMIN_ADHERENT_IMPERSONATE',
            'ROLE_ADMIN_ADHERENT_UNREGISTER',
            'ROLE_ADMIN_POLITIQUE_ADHERENT_ELECTED_REPRESENTATIVES',
            'ROLE_ADMIN_TERRITOIRES_COMMITTEES',
            'ROLE_ADMIN_TERRITOIRES_COMMITTEE_MERGES',
            'ROLE_ADMIN_TERRITOIRES_COMMITTEE_DESIGNATION',
            'ROLE_ADMIN_TERRITOIRES_EVENTS',
            'ROLE_ADMIN_TERRITOIRES_EVENT_CATEGORIES',
            'ROLE_ADMIN_TERRITOIRES_EVENT_GROUP_CATEGORIES',
            'ROLE_ADMIN_TERRITOIRES_REPORTS',
            'ROLE_ADMIN_TERRITOIRES_PROCURATION_REQUESTS',
            'ROLE_ADMIN_TERRITOIRES_PROCURATION_PROXIES',
            'ROLE_ADMIN_TERRITOIRES_ELECTIONS',
            'ROLE_ADMIN_TECH_EMAIL_LOGS',
            'ROLE_ADMIN_ARCHIVES_REFERENT_TAGS',
            'ROLE_ADMIN_TERRITOIRES_REFERENTS',
            'ROLE_ADMIN_TERRITOIRES_REFERENT_AREAS',
            'ROLE_ADMIN_IDEES_OLDOLF',
            'ROLE_ADMIN_TECH_OAUTH_CLIENTS',
            'ROLE_ADMIN_TECH_SCOPES',
            'ROLE_ADMIN_TERRITOIRES_ORGANIZATIONAL_CHART_ITEMS',
            'ROLE_ADMIN_FORMATION_MOOC',
            'ROLE_ADMIN_TECH_SUBSCRIPTION_TYPES',
            'ROLE_ADMIN_TECH_USER_LIST_DEFINITIONS',
            'ROLE_ADMIN_RENAISSANCE_BIOGRAPHY_EXECUTIVE_OFFICE_MEMBERS',
            'ROLE_ADMIN_APPLICATION_MOBILE_NOTIFICATIONS',
            'ROLE_ADMIN_APPLICATION_MOBILE_NATIONAL_NEWS',
            'ROLE_ADMIN_APPLICATION_MOBILE_REGIONAL_NEWS',
            'ROLE_ADMIN_APPLICATION_MOBILE_DEPARTMENTAL_NEWS',
            'ROLE_ADMIN_APPLICATION_MOBILE_SUGGESTED_QUESTIONS',
            'ROLE_ADMIN_APPLICATION_MOBILE_LOCAL_SURVEYS',
            'ROLE_ADMIN_APPLICATION_MOBILE_NATIONAL_SURVEYS',
            'ROLE_ADMIN_APPLICATION_MOBILE_NATIONAL_POLLS',
            'ROLE_ADMIN_APPLICATION_MOBILE_RIPOSTES',
            'ROLE_ADMIN_APPLICATION_MOBILE_RESSOURCE_LINKS',
            'ROLE_ADMIN_APPLICATION_MOBILE_HEADER_BLOCKS',
            'ROLE_ADMIN_APPLICATION_MOBILE_DEEP_LINKS',
            'ROLE_ADMIN_TERRITOIRES_VOTE_PLACES',
            'ROLE_ADMIN_TERRITOIRES_ASSESSOR_REQUESTS',
            'ROLE_ADMIN_POLITIQUE_APPLICATION_REQUEST_TECHNICAL_SKILLS',
            'ROLE_ADMIN_POLITIQUE_APPLICATION_REQUEST_THEMES',
            'ROLE_ADMIN_POLITIQUE_APPLICATION_REQUEST_TAGS',
            'ROLE_ADMIN_POLITIQUE_APPLICATION_REQUEST_RUNNING_MATE_REQUESTS',
            'ROLE_ADMIN_POLITIQUE_APPLICATION_REQUEST_VOLUNTEER_REQUESTS',
            'ROLE_ADMIN_IDEES_CHEZ_VOUS',
            'ROLE_ADMIN_FINANCES_DONATIONS',
            'ROLE_ADMIN_RENAISSANCE_CREATE_ADHERENT',
            'ROLE_ADMIN_SOCLE_PROGRAMMATIQUE_ALL',
            'ROLE_ADMIN_POLITIQUE_ELECTION_CITY_CARDS',
            'ROLE_ADMIN_POLITIQUE_ELECTION_CITY_CARD_MANAGERS',
            'ROLE_ADMIN_POLITIQUE_ELECTED_REPRESENTATIVES',
            'ROLE_ADMIN_TERRITOIRES_FILES',
            'ROLE_ADMIN_TERRITOIRES_TEAMS',
            'ROLE_ADMIN_TERRITOIRES_TEAM_MEMBER_HISTORIES',
            'ROLE_ADMIN_PHONING_CAMPAIGNS',
            'ROLE_ADMIN_PHONING_CAMPAIGN_HISTORIES',
            'ROLE_ADMIN_PORTE_A_PORTE_CAMPAIGNS',
            'ROLE_ADMIN_PORTE_A_PORTE_CAMPAIGN_HISTORIES',
            'ROLE_ADMIN_COMMUNICATION_QR_CODES',
            'ROLE_ADMIN_COMMUNICATION_CMS_BLOCKS',
            'ROLE_ADMIN_TERRITOIRES_JME_DOCUMENTS',
            'ROLE_ADMIN_TERRITOIRES_JME_GENERAL_MEETING_REPORTS',
            'ROLE_ADMIN_TERRITOIRES_JME_EMAIL_TEMPLATES',
            'ROLE_ADMIN_TERRITOIRES_ELUS_NOTIFICATION',
            'ROLE_ADMIN_INSTANCES_TERRITORIAL_COUNCILS',
        ]);

        $writerRoles = $this->getRoles([
            'ROLE_ADMIN_COMMUNICATION_MEDIAS',
            'ROLE_ADMIN_COMMUNICATION_ARTICLES',
            'ROLE_ADMIN_COMMUNICATION_ARTICLE_CATEGORIES',
            'ROLE_ADMIN_COMMUNICATION_PAGES',
            'ROLE_ADMIN_ARCHIVES_CONTENU',
            'ROLE_ADMIN_ARCHIVES_JE_PARTAGE',
            'ROLE_ADMIN_COMMUNICATION_HOME_BLOCKS',
            'ROLE_ADMIN_COMMUNICATION_LIVE_LINKS',
            'ROLE_ADMIN_ARCHIVES_PROPOSALS',
            'ROLE_ADMIN_ARCHIVES_EXPLICATIONS',
            'ROLE_ADMIN_ARCHIVES_FACEBOOK_PROFILES',
            'ROLE_ADMIN_COMMUNICATION_REDIRECTIONS',
        ]);

        $superAdmin2fa = $this->administratorFactory->createFromArray([
            'email' => 'titouan.galopin@en-marche.fr',
            'password' => 'secret!12345',
            'roles' => $adminRoles,
            'secret' => 'D3GU3BR4LUDK5NWR',
        ]);

        $admin = $this->administratorFactory->createFromArray([
            'email' => 'jean.dupond@en-marche.fr',
            'password' => 'secret!12345',
            'roles' => $adminRoles,
            'activated' => false,
        ]);
        $this->setReference('administrator-1', $admin);

        $writer = $this->administratorFactory->createFromArray([
            'email' => 'martin.pierre@en-marche.fr',
            'password' => 'secret!12345',
            'roles' => $writerRoles,
        ]);

        $manager->persist($this->administratorFactory->createFromArray([
            'email' => 'admin@en-marche-dev.fr',
            'password' => 'admin',
            'roles' => $adminRoles,
        ]));

        $manager->persist($superadmin = $this->administratorFactory->createFromArray([
            'email' => 'superadmin@en-marche-dev.fr',
            'password' => 'superadmin',
            'roles' => $this->getRoles(['ROLE_SUPER_ADMIN']),
        ]));
        $this->setReference('administrator-2', $superadmin);

        $manager->persist($this->administratorFactory->createFromArray([
            'email' => 'writer@en-marche-dev.fr',
            'password' => 'writer',
            'roles' => $writerRoles,
        ]));

        $manager->persist($renaissanceAdmin = $this->administratorFactory->createFromArray([
            'email' => 'admin@renaissance.code',
            'password' => 'renaissance',
            'roles' => $this->getRoles([
                'ROLE_ADMIN_RENAISSANCE_COMMITMENTS',
                'ROLE_ADMIN_RENAISSANCE_ARTICLES',
                'ROLE_ADMIN_RENAISSANCE_HOME_BLOCKS',
                'ROLE_ADMIN_RENAISSANCE_BIOGRAPHY_EXECUTIVE_OFFICE_MEMBERS',
                'ROLE_ADMIN_RENAISSANCE_ADHERENT_FORMATIONS',
                'ROLE_ADMIN_RENAISSANCE_DEPARTMENT_SITES',
            ]),
        ]));
        $this->setReference('administrator-renaissance', $renaissanceAdmin);

        $manager->persist($superAdmin2fa);
        $manager->persist($admin);
        $manager->persist($writer);
        $manager->flush();
    }

    private function getRoles(array $roleCodes): array
    {
        $roles = [];

        foreach ($roleCodes as $roleCode) {
            $roles[] = $this->getReference("administrator-role-$roleCode");
        }

        return $roles;
    }

    public function getDependencies()
    {
        return [
            LoadAdministratorRoleData::class,
        ];
    }
}
