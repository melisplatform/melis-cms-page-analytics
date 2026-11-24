-- La table n'avait que sa clé primaire : la déduplication par visiteur et le plafond
-- journalier payaient un full scan à chaque hit front.
ALTER TABLE `melis_cms_page_analytics`
  ADD INDEX `idx_mcpa_page_session` (`ph_page_id`, `ph_session_id`),
  ADD INDEX `idx_mcpa_page_date` (`ph_page_id`, `ph_date_visit`);

-- Les identifiants visiteur historiques sont remplacés par leur empreinte — même famille
-- que la clé visiteur hachée.
UPDATE `melis_cms_page_analytics` SET `ph_session_id` = SHA2(`ph_session_id`, 256);
