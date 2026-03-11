<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudinaryService
{
    public function uploadImage(UploadedFile $file, string $folder = 'denuncias'): array
    {
        $cloudName = (string) config('services.cloudinary.cloud_name');
        $apiKey = (string) config('services.cloudinary.api_key');
        $apiSecret = (string) config('services.cloudinary.api_secret');
        $uploadPreset = (string) config('services.cloudinary.upload_preset');

        if ($cloudName === '') {
            throw new RuntimeException('Cloudinary no esta configurado: falta CLOUDINARY_CLOUD_NAME.');
        }

        $endpoint = sprintf('https://api.cloudinary.com/v1_1/%s/image/upload', $cloudName);

        $payload = [
            'folder' => $folder,
        ];

        if ($uploadPreset !== '') {
            $payload['upload_preset'] = $uploadPreset;
        } else {
            if ($apiKey === '' || $apiSecret === '') {
                throw new RuntimeException('Cloudinary no esta configurado: falta API key/secret o upload preset.');
            }

            $timestamp = time();
            $signedParams = [
                'folder' => $folder,
                'timestamp' => $timestamp,
            ];

            $payload['api_key'] = $apiKey;
            $payload['timestamp'] = $timestamp;
            $payload['signature'] = $this->buildSignature($signedParams, $apiSecret);
        }

        $response = Http::attach(
            'file',
            fopen($file->getRealPath(), 'r'),
            $file->getClientOriginalName()
        );

        // Desactivar verificación SSL en desarrollo
        if (app()->isLocal()) {
            $response = $response->withoutVerifying();
        }

        $response = $response->post($endpoint, $payload);

        if (!$response->successful()) {
            throw new RuntimeException('Error subiendo imagen a Cloudinary.');
        }

        $data = $response->json();
        $url = $data['secure_url'] ?? $data['url'] ?? null;

        if (!$url) {
            throw new RuntimeException('Cloudinary no devolvio URL de imagen.');
        }

        return [
            'url' => $url,
            'public_id' => $data['public_id'] ?? null,
        ];
    }

    public function deleteImage(?string $publicId): void
    {
        if (!$publicId) {
            return;
        }

        $cloudName = (string) config('services.cloudinary.cloud_name');
        $apiKey = (string) config('services.cloudinary.api_key');
        $apiSecret = (string) config('services.cloudinary.api_secret');

        if ($cloudName === '' || $apiKey === '' || $apiSecret === '') {
            return;
        }

        $timestamp = time();
        $signedParams = [
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ];

        $request = Http::asForm();

        if (app()->isLocal()) {
            $request = $request->withoutVerifying();
        }

        $request->post(
            sprintf('https://api.cloudinary.com/v1_1/%s/image/destroy', $cloudName),
            [
                'public_id' => $publicId,
                'api_key' => $apiKey,
                'timestamp' => $timestamp,
                'signature' => $this->buildSignature($signedParams, $apiSecret),
            ]
        );
    }

    private function buildSignature(array $params, string $apiSecret): string
    {
        ksort($params);

        $signatureBase = collect($params)
            ->map(fn ($value, $key) => sprintf('%s=%s', $key, $value))
            ->implode('&');

        return sha1($signatureBase.$apiSecret);
    }
}
