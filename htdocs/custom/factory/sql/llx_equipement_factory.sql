-- --------------------------------------------------------

--
-- Structure de la table llx_equipement_factory
--
-- Contient le lien entre les equipements et factory

-- composant r�serv� 0 si fabriqu� (permanent), 1 si composant (temporaire)
CREATE TABLE llx_equipement_factory (
  fk_equipement integer NOT NULL DEFAULT 0,
  fk_factory 	integer NOT NULL DEFAULT 0,
  children 		integer NOT NULL DEFAULT 0,	
  UNIQUE KEY uk_factory_equipement (fk_equipement, fk_factory)
) ENGINE=InnoDB ;