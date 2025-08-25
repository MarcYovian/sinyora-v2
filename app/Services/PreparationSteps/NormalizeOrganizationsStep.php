<?php

namespace App\Services\PreparationSteps;

use App\Repositories\Contracts\OrganizationRepositoryInterface;
use Atomescrochus\StringSimilarities\Compare;
use Closure;
use Illuminate\Support\Facades\Log;

class NormalizeOrganizationsStep implements PreparationStepInterface
{
    public function __construct(
        protected OrganizationRepositoryInterface $organizationRepository
    ) {}

    public function handle(array $data, Closure $next): array
    {
        Log::info('NormalizeOrganizationsStep memulai');
        try {
            $data = $this->process($data);
            Log::info('NormalizeOrganizationsStep selesai');
            return $next($data);
        } catch (\Exception $e) {
            Log::error('NormalizeOrganizationsStep gagal: ' . $e->getMessage());

            $data['processing_errors'][] = [
                'step' => 'NormalizeOrganizationsStep',
                'error' => $e->getMessage(),
                'timestamp' => now()
            ];

            return $next($data);
        }
    }
    public function process(array $data): array
    {
        $orgs = data_get($data, 'document_information.emitter_organizations', []);

        // --- PINDAHKAN SEMUA LOGIKA fuzzy matching organisasi ke sini ---
        $masterOrganizations = $this->organizationRepository->getActiveOrganizations();
        $comparison = new Compare();

        $similarityThreshold = 0.7;

        foreach ($orgs as &$org) {
            $org['original_name'] = $org['name'];
            $extractedName = strtolower(trim($org['name']));
            $bestMatch = null;
            $highestScore = 0;

            foreach ($masterOrganizations as $masterOrg) {
                $masterOrgName = strtolower(trim($masterOrg->name));
                $score = $comparison->jaroWinkler($extractedName, $masterOrgName);

                if ($score > $highestScore) {
                    $highestScore = $score;
                    $bestMatch = $masterOrg;
                }
            }

            if ($bestMatch && $highestScore >= $similarityThreshold) {
                $org['nama_organisasi'] = $bestMatch->name;
                $org['nama_organisasi_id'] = $bestMatch->id;
                $org['match_status'] = 'matched';
                $org['similarity_score'] = round($highestScore, 2);
            } else {
                $org['nama_organisasi_id'] = null;
                $org['match_status'] = 'unmatched';
                $org['similarity_score'] = round($highestScore, 2);
            }
        }

        unset($org);

        $matchedOrgs = array_filter($orgs, function ($org) {
            return $org['match_status'] === 'matched';
        });

        if (count($matchedOrgs) === 1) {
            $data['document_information']['final_organization_id'] = $matchedOrgs[0]['nama_organisasi_id'];
        } else {
            $data['document_information']['final_organization_id'] = null;
        }

        data_set($data, 'document_information.emitter_organizations', $orgs);
        return $data;
    }
}
