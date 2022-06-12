<?php

namespace App\Http\Controllers;


use App\Models\Listing;
use Clockwork\Request\RequestType;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
    // show all listings
    public function index(Request $request) {
        $listings = Listing::latest()->filter(request(['tag','search']))->paginate(6);
        return view('listings.index', compact('listings'));
    }
    // show single listing
    public function show($id) {
        $listing = Listing::findOrFail($id);
        return view('listings.show', compact('listing'));
    }

    //Show create form
    public function create()
    {
        return view('listings.create');
    }

    //store listings data
    public function store(Request $request)
    {
        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required', Rule::unique('listings','company')],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required','email'],
            'tags' => 'required',
            'description' => 'required',
        ]);
        if($request->hasFile('logo'))
        {
            $formFields['logo'] = $request->file('logo')->store('logos','public');
        }
        $formFields['user_id'] = auth()->id();
        Listing::create($formFields);


        return redirect('/')->with('message','listing created successfully!');
    }

    //shoew edit form
    public function edit($id)
    {
        $listing = Listing::findOrFail($id);
        return view('listings.edit',compact('listing'));
    }

    public function update(Request $request, listing $listing)
    {
        if ($listing->user_id != auth()->id()){
            abort(403, 'Unauthorized Action');
        }
        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required'],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required','email'],
            'tags' => 'required',
            'description' => 'required',
        ]);
        if($request->hasFile('logo'))
        {
            $formFields['logo'] = $request->file('logo')->store('logos','public');
        }
        $listing->update($formFields);


        return back()->with('message','listing updated successfully!');
    }
    public function destroy(Listing $listing)
    {
        if ($listing->user_id != auth()->id()){
            abort(403, 'Unauthorized Action');
        }
        $listing->delete();
        return redirect('/')->with('message','listing d eleted successfully!');
    }

    // Manage Listings
  public function manage()
  {
      return view('listings.manage', ['listings' => auth()->user()->listings()->get()]);
  }
}