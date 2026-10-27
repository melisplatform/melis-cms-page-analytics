-- SECURITY (audit PA-H1 / PA-M2)

-- La table n'avait que sa clé primaire : la déduplication par visiteur et le plafond
-- journalier payaient un full scan à chaque hit front.
ALTER TABLE `melis_cms_page_analytics`
  ADD INDEX `idx_mcpa_page_session` (`ph_page_id`, `ph_session_id`),
  ADD INDEX `idx_mcpa_page_date` (`ph_page_id`, `ph_date_visit`);

-- Les lignes historiques stockent le PHPSESSID BRUT des visiteurs (matériau de session) :
-- on les remplace par leur empreinte — même famille que la nouvelle clé visiteur hachée,
-- plus aucune donnée brute en base.
UPDATE `melis_cms_page_analytics` SET `ph_session_id` = SHA2(`ph_session_id`, 256);
