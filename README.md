# Sequence Generator Bundle

Ce bundle a pour objectif de simplifier l'utilisation de séquences configurable
pour la génération de code. Par exemple, il permet de générer facilement des
références type code facture de la forme `FC2018-0001`.

**Ce bundle ne fonctionne que pour les bases de données PostreSQL**

## Installation

```
composer require kinulab/sequence-generator-bundle
```

Ajouter dans `app/AppKernel.php` :

``` php
    public function registerBundles()
    {
        $bundles = array(
            ...
            new Kinulab\SequenceGeneratorBundle\SequenceGeneratorBundle(),
        );
    }
```

## Utilisation

Création d'une nouvelle séquence :

``` php
use Kinulab\SequenceGeneratorBundle\Entity\CustomSequence;
use Kinulab\SequenceGeneratorBundle\Generator\SequenceGenerator;

// Enregistrement d'une nouvelle séquence
$sequence = new CustomSequence();
$sequence->setLibelle("Séquence des factures");
$sequence->setSequenceName("facture_seq");
$sequence->setIncrementBy(1);
$sequence->setIncrementLength(5);
$sequence->setPrefix('FC');
$sequence->setRestartYearly(true);

$em = $doctrine->getManager();
$em->persist($sequence);
$em->flush();

// Initialisation de la séquence
$generator = $container->get(SequenceGenerator::class);
$generator->initializeSequence($sequence);

// Utilisation
echo $generator->getNextVal('facture_seq'); // FC00001
echo $generator->getNextVal('facture_seq'); // FC00002

// On change le pas d'incrémentation
$sequence->setIncrementBy(2);
$generator->initializeSequence($sequence);

echo $generator->getNextVal('facture_seq'); // FC00004
echo $generator->getNextVal('facture_seq'); // FC00006
```

## Utilisation de marqueurs

Il est possible d'inclure de le préfix ou le suffix de la séquence un ou plusieurs
marqueurs. Les marqueurs sont de la forme `%<nom du marquer>%`.

Les marqeurs utilisables sont :

| Marqeur       | Description                                    | Exemple |
| ------------- | ---------------------------------------------- | ------- |
| `year`        | Année sur 4 chiffres                           | 2018    |
| `y`           | Année sur 2 chiffres                           | 18      |
| `month`       | Mois sur 2 chiffres                            | 05      |
| `day`         | Jour sur 2 chiffres                            | 25      |
| `doy`         | Jour de l'année (0 à 365)                      | 84      |
| `woy`         | Semaine de l'année (0 à 52)                    | 42      |
| `weekday`     | Jour de la semaine de 0 (dimanche) à 6 (lundi) | 5       |
| `h24`         | Heure au format 24h                            | 17      |
| `h12`         | Heure au format 12h                            | 6       |
| `min`         | Minute                                         | 35      |
| `sec`         | Seconde                                        | 42      |

Exemple :

``` php
$sequence->setPrefix('FC%year%-%month%-');
echo $generator->getNextVal('facture_seq'); // FC2018-08-*****
```

Il est possible également d'inclure des marqueurs dépendant de l'objet passé en
second paramètre de la méthode `getNextVal`.

Exemple :

``` php
$facture = new stdClass();
$facture->codeClient = 'ABC';
$facture->userName = 'homer';

$sequence->setPrefix('FC-%object.codeClient%-');
$sequence->setSuffix('-%object.userName%');

echo $generator->getNextVal('facture_seq', $facture); // FC-ABC-*****-homer
```
