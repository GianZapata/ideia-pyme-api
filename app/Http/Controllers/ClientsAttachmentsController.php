<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientsAttachmentsRequest;
use App\Http\Requests\UpdateClientsAttachmentsRequest;
use App\Models\Attachment;
use App\Models\Client;
use App\Models\ClientsAttachments;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ClientsAttachmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {

    //     // Upload files to storage and save to database and link with ClientsAttachments
    //     $client = Client::find($request->client_id);

    //     foreach ($request->file('attachments') as $file) {
    //         $attachment = Attachment::create([
    //             'name' => $file->getClientOriginalName(),
    //             'type' => $file->getMimeType(),
    //             'size' => $file->getSize(),
    //             'extension' => $file->getClientOriginalExtension(),
    //             'url' => $file->store('attachments'),
    //         ]);

    //         $client->attachments()->attach($attachment->id);
    //     }

    //     return redirect()->back()->with('success', 'Files uploaded successfully.');


    // }
    public function store(Request $request, $clientId)
    {
        $attachments = [];
        try {

            $client = Client::find($clientId);
            if(!$client) {
                return response()->json([
                    'message' => 'No se encontro el cliente',
                    'errors' => [
                        'client' => 'No se encontro el cliente'
                    ]
                ], 401);
            }

            foreach ($request->file('attachments') as $generalFile) {
                $attachment = $this->createAttachmentFromUploadedFile($generalFile);
                // create clients attachments
                $clientsAttachment = ClientsAttachments::create([
                    'client_id' => $client->id,
                    'attachment_id' => $attachment->id,
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Hubo un error al cargar los archivos',
                'errors'  => [
                    'file' => 'Hubo un error al cargar los archivos.',
                    'message' => $th->getMessage(),
                ]
            ], 500);
        }
        

        return response()->json(['attachments' => $attachments], 201);
    }

    private function createAttachmentFromUploadedFile(UploadedFile $file): Attachment
    {
        $originalName = $file->getClientOriginalName();
        $mime = $file->getClientMimeType();
        $extension = $file->getClientOriginalExtension();
        $size = $file->getSize();
        $hash = hash_file('md5', $file->getRealPath());

        $name = Str::uuid();
        $pathToSave = "client/files/";
        $diskToSave = "public";

        $attachment = Attachment::create([
            'name' => $name,
            'original_name' => $originalName,
            'mime' => $mime,
            'extension' => $extension,
            'size' => $size,
            'sort' => 0,
            'path' => $pathToSave,
            'description' => null,
            'alt' => null,
            'hash' => $hash,
            'disk' => $diskToSave,
            'group' => null,
        ]);

        $attachment->uploadFile($file);

        return $attachment;
    }

    /**
     * Display the specified resource.
     */
    public function show(ClientsAttachments $clientsAttachments)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClientsAttachments $clientsAttachments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientsAttachmentsRequest $request, ClientsAttachments $clientsAttachments)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClientsAttachments $clientsAttachments)
    {
        //
    }
}
