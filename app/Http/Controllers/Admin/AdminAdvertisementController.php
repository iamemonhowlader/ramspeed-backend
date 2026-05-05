<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminAdvertisementController extends Controller
{
    public function index()
    {
        $advertisements = Advertisement::orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'data' => $advertisements]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'ad_location' => 'required|string',
                'ad_link' => 'required|url',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $validated['ad_location'] . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('advertisements', $imageName, 'public');
            }

            $advertisement = Advertisement::create([
                'ad_location' => $validated['ad_location'],
                'ad_link' => $validated['ad_link'],
                'image_path' => $imagePath,
                'active' => 'yes'
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Advertisement added successfully',
                'data' => $advertisement
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $advertisement = Advertisement::findOrFail($id);
        return response()->json(['success' => true, 'data' => $advertisement]);
    }

    public function update(Request $request, $id)
    {
        try {
            $advertisement = Advertisement::findOrFail($id);

            $validated = $request->validate([
                'ad_location' => 'required|string',
                'ad_link' => 'required|url',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $updateData = [
                'ad_location' => $validated['ad_location'],
                'ad_link' => $validated['ad_link']
            ];

            if ($request->hasFile('image')) {
                // Delete old image
                if ($advertisement->image_path) {
                    Storage::disk('public')->delete($advertisement->image_path);
                }

                // Upload new image
                $image = $request->file('image');
                $imageName = time() . '_' . $validated['ad_location'] . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('advertisements', $imageName, 'public');
                $updateData['image_path'] = $imagePath;
            }

            $advertisement->update($updateData);

            return response()->json([
                'success' => true, 
                'message' => 'Advertisement updated successfully',
                'data' => $advertisement
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $advertisement = Advertisement::findOrFail($id);
            $advertisement->active = $advertisement->active === 'yes' ? 'no' : 'yes';
            $advertisement->save();

            return response()->json([
                'success' => true, 
                'message' => 'Advertisement status updated successfully',
                'data' => $advertisement
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $advertisement = Advertisement::findOrFail($id);

            // Delete image file
            if ($advertisement->image_path) {
                Storage::disk('public')->delete($advertisement->image_path);
            }

            $advertisement->delete();

            return response()->json([
                'success' => true, 
                'message' => 'Advertisement deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getByLocation($location)
    {
        try {
            $advertisement = Advertisement::where('ad_location', $location)
                ->where('active', 'yes')
                ->first();

            if ($advertisement) {
                return response()->json([
                    'success' => true, 
                    'data' => $advertisement
                ]);
            }

            return response()->json([
                'success' => false, 
                'message' => 'No active advertisement found for this location'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
