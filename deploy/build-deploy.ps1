# =====================================================================
#  deploy/build-deploy.ps1
#  Construit un ZIP « à plat » prêt à décompresser dans htdocs/
#  (structure InfinityFree : la racine du zip = le contenu de htdocs/).
#
#  Le zip N'EST PAS versionné (voir .gitignore) : c'est un artefact.
#  Ce script, lui, est versionné → le zip est reconstructible à tout moment.
#
#  Lancer :  powershell -ExecutionPolicy Bypass -File deploy\build-deploy.ps1
# =====================================================================
$ErrorActionPreference = 'Stop'

$proj  = Split-Path -Parent $PSScriptRoot          # racine du projet (deploy/ est dessous)
# Zone de préparation SOUS le projet (chemin sans espace ni forme 8.3 : évite
# les décalages de longueur de chemin lors du calcul des noms d'entrée du zip).
$stage = Join-Path $proj 'deploy\_stage'
$zip   = Join-Path $proj 'deploy\digital-smile-deploy.zip'

# 1) Nettoyage de la zone de préparation.
if (Test-Path $stage) { Remove-Item $stage -Recurse -Force }
New-Item -ItemType Directory -Path $stage | Out-Null

# 2) CONTENU de public/  -> racine du zip (index.php à la racine, pas sous public/).
Copy-Item "$proj\public\*" $stage -Recurse -Force
Copy-Item "$proj\public\.htaccess" $stage -Force          # fichier caché (le glob * peut l'oublier)
# On NE livre PAS les scripts d'installation / de seed (setup uniquement, risque en prod).
Remove-Item "$stage\install.php","$stage\seed.php" -Force -ErrorAction SilentlyContinue
# uploads/ : garder seulement les garde-fous (.htaccess + index.html), jamais les fichiers déposés.
if (-not (Test-Path "$stage\uploads")) { New-Item -ItemType Directory "$stage\uploads" | Out-Null }
Get-ChildItem "$stage\uploads" -File -Force |
    Where-Object { $_.Name -notin @('.htaccess','index.html') } | Remove-Item -Force

# 3) app/ , config/ , lang/  -> dossiers frères à la racine du zip.
Copy-Item "$proj\app"    $stage -Recurse -Force           # inclut app/.htaccess (blocage)
Copy-Item "$proj\config" $stage -Recurse -Force           # inclut config/.htaccess (blocage)
Copy-Item "$proj\lang"   $stage -Recurse -Force
# JAMAIS de secret ni de modèle dans le zip.
Remove-Item "$stage\config\config.local.php" -Force -ErrorAction SilentlyContinue
Remove-Item "$stage\config\config.local.php.example" -Force -ErrorAction SilentlyContinue

# 4) Forcer index.php comme index du dossier (certains hôtes préfèrent index.html,
#    ce qui masquerait l'appli PHP). On préfixe le .htaccess racine. UTF-8 SANS BOM
#    (un BOM en tête de .htaccess casserait Apache).
$ht  = Join-Path $stage '.htaccess'
$new = "DirectoryIndex index.php index.html`r`n" + (Get-Content $ht -Raw)
[System.IO.File]::WriteAllText($ht, $new, (New-Object System.Text.UTF8Encoding($false)))

# 5) Compression : CONTENU de $stage à la racine du zip (inclut les .htaccess,
#    pas de wrapper). On ajoute chaque fichier À LA MAIN avec des séparateurs
#    « / » (et non « \ ») : indispensable pour une extraction correcte sur un
#    hôte Linux (InfinityFree). CreateFromDirectory de .NET Framework mettrait
#    des « \ », ce qui créerait des fichiers aux noms cassés sous Linux.
if (Test-Path $zip) { Remove-Item $zip -Force }
Add-Type -AssemblyName System.IO.Compression.FileSystem
# Préfixe issu du MÊME fournisseur que les enfants (Get-Item / Get-ChildItem) →
# même forme de chemin, pas de décalage court/long.
$stageFull = (Get-Item -LiteralPath $stage).FullName.TrimEnd('\')
$archive = [System.IO.Compression.ZipFile]::Open($zip, 'Create')
try {
    Get-ChildItem -LiteralPath $stage -Recurse -File -Force | ForEach-Object {
        # Retire le préfixe, enlève le séparateur de tête éventuel, puis \ -> /.
        $rel = ($_.FullName.Substring($stageFull.Length).TrimStart('\', '/') -replace '\\', '/')
        [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($archive, $_.FullName, $rel) | Out-Null
    }
} finally {
    $archive.Dispose()
}

Remove-Item $stage -Recurse -Force
Write-Output "Zip construit : $zip"
