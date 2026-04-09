<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Contract;
use App\Models\UrlCounter;

class ContractController extends Controller
{
    private $apiKey = "0533ca35d76fbe299588a5c95be9cb4a20cdf4c0a3fb0c051a38061ad0df26e6";
    private $baseUrl = "https://api.vorj.app/main/v2";

    public function getContractNFTs(Request $request)
    {
        $contract = $request->contract;
        $owner = $request->owner;
        $lastItemPrimary = $request->lastItemPrimary;
        $limit = $request->limit ?? 15;

        try {
            $url = $owner 
                ? "{$this->baseUrl}/blockchain/nft/{$owner}"
                : "{$this->baseUrl}/erc721/contracts/{$contract}";

            $params = [
                'limit' => $limit,
                'sortingDirection' => 'asc',
                'sortingField' => 'tokenId',
                'page' => 1,
                'net' => 'testnet'
            ];

            if ($contract && $owner) {
                $params['contractAddress'] = $contract;
            }

            if ($lastItemPrimary) {
                $params['lastItemPrimary'] = $lastItemPrimary;
                $params['sortingField'] = 'tokenId';
            }

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey
            ])->get($url, $params);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    // public function getMetadata(Request $request)
    // {
    //     try {
    //         $metadataUri = $request->uri;
    //         $gatewayUrl = 'https://arweave.net/'; // Arweave gateway
    //         $url = str_replace('ar://', $gatewayUrl, $metadataUri);
    
    //         $response = Http::get($url);
    //         $metadata = $response->json();
    
    //         $imageUrl = str_replace('ar://', 'https://arweave.net/', 
    //             $metadata['image'] ?? $metadata['image_url'] ?? '');
    
    //         return response()->json(['imageUrl' => $imageUrl, 'url' => $request->uri, 'url2' => $url]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => $e->getMessage(), 
    //             'url' => $request->uri, 
    //             'url2' => $imageUrl ?? 'error', 
    //             'meta' => $metadata ?? []
    //         ], 500);
    //     }
    // }

    public function getMetadata(Request $request)
    {
        try {
            $metadataUri = $request->uri;
            $gatewayUrl = 'https://copper-key-guppy-230.mypinata.cloud/ipfs/';
            $url = str_replace('ipfs://', $gatewayUrl, $metadataUri);

            $response = Http::get($url);
            $metadata = $response->json();

            $imageUrl = str_replace('ipfs://', 'https://chocolate-junior-bass-271.mypinata.cloud/ipfs/', 
                $metadata['image'] ?? $metadata['image_url'] ?? '');

            return response()->json(['imageUrl' => $imageUrl, 'url' => $request->uri, 'url2' => $url]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'url' => $request->uri, 'url2' => $imageUrl ?? 'error', "meta" => $metadata], 500);
        }
    }

    public function getWeeklyContracts()
    {
        $startOfWeek = now()->startOfWeek();
        
        $contracts = Contract::all();

        return response()->json($contracts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'contract_address' => 'required_without:owner_address',
            'owner_address' => 'required_without:contract_address',
            'date' => 'required|date',
        ]);

        $contract = Contract::create([
            'contract_address' => $request->contract_address ?? "oops",
            'owner_address' => $request->owner_address ?? "oops",
            'date' => $request->date,
        ]);
        
        return response()->json($contract, 201);
    }

    public function destroy($id)
    {
        $contract = Contract::findOrFail($id);
        $contract->delete();
        return response()->json(['message' => 'Contract deleted'], 200);
    }
    
    public function setNftDB()
    {
        for($i=1; $i <= 333; $i++) {
            UrlCounter::create(["used" => false]);
        }
        
        return response()->json(["message" => "urls init successfully"]);
    }
    
    public function getLink()
    {
        $urlcount = UrlCounter::where('used', false)->inRandomOrder()->first()->id ?? null;

        return response()->json([
            "url" => $urlcount ? "https://rapidcrewtechgh.com/uploads/metadata/{$urlcount}.json" : null,
            "id" => $urlcount
        ]);
    }
    
    public function linkExpire(UrlCounter $id)
    {
        $id->update(['used' => true]);
        return response()->json(["message" => "link expired"]);
    }
    
    public function getNftUrl()
    {
        
    }
}
