<?php

namespace App\Controller;

class FormateurController
{
    public function liste(): string
    {
        $formateurs = [
            [
                'nom' => 'Jean Dupont',
                'specialite' => 'Développement Web',
                'experience' => '10 ans',
                'email' => 'jean.dupont@skillhub.com',
                'bio' => 'Expert en JavaScript, React et Node.js avec plus de 10 ans d\'expérience dans le développement web.',
            ],
            [
                'nom' => 'Marie Martin',
                'specialite' => 'Design UX/UI',
                'experience' => '8 ans',
                'email' => 'marie.martin@skillhub.com',
                'bio' => 'Spécialiste en UX/UI design, passionnée par l\'accessibilité et les interfaces modernes.',
            ],
            [
                'nom' => 'Pierre Durand',
                'specialite' => 'Base de données',
                'experience' => '12 ans',
                'email' => 'pierre.durand@skillhub.com',
                'bio' => 'Architecte de bases de données expert, maîtrisant MySQL, PostgreSQL et MongoDB.',
            ],
            [
                'nom' => 'Sophie Bernard',
                'specialite' => 'DevOps & Cloud',
                'experience' => '6 ans',
                'email' => 'sophie.bernard@skillhub.com',
                'bio' => 'Ingénieure DevOps, spécialisée en Docker, Kubernetes et CI/CD.',
            ],
            [
                'nom' => 'Lucas Moreau',
                'specialite' => 'Cybersécurité',
                'experience' => '9 ans',
                'email' => 'lucas.moreau@skillhub.com',
                'bio' => 'Expert en cybersécurité, spécialisé en sécurisation d\'applications web.',
            ],
        ];

        ob_start();
        require __DIR__ . '/../../templates/formateur/liste.html.twig';
        return ob_get_clean();
    }
}
