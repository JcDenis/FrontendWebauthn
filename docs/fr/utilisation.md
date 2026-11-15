# FrontendWebauthn : guide d'utilisation

FrontendWebauthn permet à vos visiteurs de se connecter au blog avec une clé d'accès (passkey) : empreinte, reconnaissance du visage, code de l'appareil ou clé de sécurité USB, au lieu d'un mot de passe. Il complète le plugin FrontendSession, qui doit être installé et activé.

## Table des matières

1. [Prérequis](#prérequis)
2. [Afficher le lien de connexion](#afficher-le-lien-de-connexion)
3. [Enregistrer une clé](#enregistrer-une-clé)
4. [Se connecter avec une clé](#se-connecter-avec-une-clé)
5. [Supprimer une clé](#supprimer-une-clé)
6. [Bon à savoir](#bon-à-savoir)

## Prérequis

* Le plugin FrontendSession doit être activé dans les réglages du blog (voir son guide).
* Le blog doit être servi avec un nom de domaine (ou `localhost`) et, en production, en HTTPS. Les clés d'accès ne fonctionnent pas avec une adresse IP : le plugin cache alors ses liens et la page « Mon compte » l'explique.
* Le navigateur du visiteur doit gérer les clés d'accès. Sinon, les liens du plugin restent cachés.

Le plugin n'a aucun réglage et pas de page d'administration.

## Afficher le lien de connexion

Dans **Gadgets**, ajoutez le gadget « Session publique » de FrontendSession. Quand un visiteur n'est pas connecté, le gadget affiche, sous le formulaire, le lien **Se connecter avec une clé d'accès**.

![Gadget public](img/widget-public.png)

La page de connexion de FrontendSession propose aussi ce lien (avec FrontendSession 0.45 ou plus récent).

## Enregistrer une clé

Le visiteur se connecte d'abord avec son identifiant et son mot de passe. Sur la page « Mon compte », la rubrique **Clés d'authentification** indique qu'il n'a pas encore de clé.

![Aucune clé](img/aucune-cle.png)

Il clique sur **Enregistrer une nouvelle clé**, saisit un nom pour la clé dans la fenêtre qui s'ouvre, puis valide avec son appareil. La clé apparaît dans la liste avec sa date d'enregistrement.

![Clé enregistrée](img/cle-enregistree.png)

## Se connecter avec une clé

Un visiteur déconnecté clique sur **Se connecter avec une clé d'accès** dans le gadget ou sur la page de connexion, puis valide avec son appareil. Il n'a pas à saisir d'identifiant : la clé suffit. La page se recharge et il est connecté.

![Connecté par clé](img/connecte-par-cle.png)

Si l'appareil ne possède aucune clé enregistrée pour ce blog, un message « Impossible de s'authentifier avec une clé » s'affiche.

## Supprimer une clé

Dans la rubrique « Clés d'authentification », cliquez sur **Supprimer** en face de la clé. La clé est retirée du blog ; pensez aussi à la supprimer de votre appareil si vous ne l'utilisez plus.

## Bon à savoir

* Un même appareil ne peut généralement pas enregistrer deux clés pour le même compte : le message « Cet appareil a déjà une clé pour ce compte. » s'affiche.
