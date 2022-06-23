# RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/{uuid}

SELECT concat('# ', name, ' (', website_url, ')\nRedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/', uuid, '\n')
 FROM club;


/*

# Koryo (http://www.taekwondobry.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/bry_sur_marne

# Taekwondo Club Dragon Charenton (http://www.clubdudragon.com/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/charenton

# Taekwondo Club Dragon Chatenay (http://dragontkd.chez-alice.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/chatenay

# A.s.c. Taekwondo-hapkido (http://www.taekwondochelles.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/chelles

# Taekwondo - Taekwonkido Club Chenoise (http://www.taekwondoprovins.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/chenoise

# Taekwondo-hapkido Club Crecy La Chapelle (http://www.taekwondocrecy.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/crecy_la_chapelle

# Estec Mudokwan (http://www.estecmudokwan.nl/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/hollande

# Taekwondo Club De Fontenay Tresigny (http://www.tkdgretzfontenay.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/fontenay_tresigny

# Taekwondo Club De Gretz (http://www.tkdgretzfontenay.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/gretz

# Taekwondo Club Joinville (http://taekwondojoinville.fr)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/joinville

# Taekwonkido Club De La Garde (http://taekwonkidolagarde.jimdo.com/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/la_garde

# Taekwonkido Club Du Pradet (http://taekwonkidolepradet.jimdo.com/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/le_pradet

# Taekwonkido-team Marseille (http://tkdmarseille.free.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/marseille

# Taekwondo Club Le Perreux (http://www.taekwondoperreux.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/le_perreux_sur_marne

# Taekwondo Club Le Plessis Trevise (http://www.taekwondoplessis.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/le_plessis_trevise

# Taekwondo Club Meudon (http://taekwondomeudon.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/meudon

# Taekwondo-hapkido Club Des Tigres Nogentais (http://www.taekwondonogent.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/nogent

# Taekwondo-hapkido Club Du Val Maubuee (http://www.taekwondonoisiel.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/noisiel

# Taekwondo-hapkido Club De Paris V (http://www.taekwondoparis5.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/paris_v

# Sacamp - Taekwondo-taekwonkido (http://www.taekwondoparis19.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/paris_xix

# Taekwondo-hapkido Club Des Coteaux (http://www.taekwondopommeuse.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/pommeuse

# Taekwondo-hapkido Club De Pomponne (http://www.taekwondopomponne.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/pomponne

# Taekwondo - Hapkido Club De Provins (http://www.taekwondoprovins.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/provins

# Taekwondo-hapkido Club De Roissy (http://www.taekwondoroissy.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/roissy_en_brie

# Taekwondo - Taekwonkido Club De Sourdun (http://www.taekwondoprovins.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/sourdun

# Suisse ()
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/suisse

# Taekwondo-hapkido Club De Torcy (http://www.taekwondotorcy.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/torcy

# Taekwonkido-team Toulon (http://taekwonkidotoulon.jimdo.com/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/toulon

# Taekwondo-hapkido Club De Villejuif (http://www.taekwondovillejuif.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/villejuif

# Taekwondo-hapkido Club De Villiers (http://www.taekwondovilliers.fr/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/villiers_sur_marne

# Taekwonkido Phenix (http://phenix.cenaclerm.fr)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/villeneuve_la_comtesse

# Taekwonkido-taekwondo Club De Vincennes (http://taekwonkidovincennes.fr)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/vincennes

# Taekwondo Taekwonkido Dragon Morangis (http://taekwondomorangis.jimdo.com)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/morangis

# Taekwonkido Kourou ()
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/kourou

# Taekwonkido Club De La Valette (https://taekwonkidolavalette.jimdo.com/)
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/la_valette

# Ghazal Taekwonkido ()
RedirectMatch 301 (.*) https://ceintureblanche.fr/crm/club/montreal


*/