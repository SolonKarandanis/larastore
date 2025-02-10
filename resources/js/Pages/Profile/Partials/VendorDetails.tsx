import {ChangeEvent, FormEvent, FormEventHandler, useState} from 'react'
import {useForm, usePage} from "@inertiajs/react";
import PrimaryButton from "@/Components/Core/PrimaryButton";
import InputLabel from "@/Components/Core/InputLabel";
import TextInput from "@/Components/Core/TextInput";
import InputError from "@/Components/Core/InputError";

const VendorDetails = ({className=''}:{className?:string}   ) => {
  const [showBecomeVendorConfirmation, setShowBecomeVendorConfirmation] = useState<boolean>(false);
  const [successMessage,setSuccessMessage] = useState<string>('');
  const user = usePage().props.auth.user;
  const token=usePage().props.csrf_token;

  const {
    data,
    setData,
    errors,
    post,
    processing,
    recentlySuccessful
  } =useForm({
    store_name: user.vendor.store_name || user.name,
    store_address: user.vendor.store_address,
  });

  const onStoreNameChange=(ev:ChangeEvent<HTMLInputElement>)=>{
    setData('store_name',ev.target.value.toLowerCase().replace(/\s+/g,'-'));
  };

  const onStoreAddressChange=(ev:ChangeEvent<HTMLTextAreaElement>)=>{
    setData('store_address',ev.target.value);
  };

  const becomeVendor:FormEventHandler = (ev:FormEvent<Element>)=>{
    ev.preventDefault();
    post(route('vendor.store'),{
      preserveScroll:true,
      onSuccess:()=>{
        toggleModal(false);
        setSuccessMessage('You can now create and publish products.')
      }
    });
  }

  const updateVendor:FormEventHandler = (ev:FormEvent<Element>)=>{
    ev.preventDefault();
    post(route('vendor.store'),{
      preserveScroll:true,
      onSuccess:()=>{
        toggleModal(false);
        setSuccessMessage('Your details were updated.')
      }
    });
  };

  const toggleModal=(value:boolean)=>{
    setShowBecomeVendorConfirmation(value);
  }



  return (
    <section className={className}>
      {recentlySuccessful && (
        <div className="toast toast-top toast-end">
          <div className="alert alert-success">
            <span>{successMessage}</span>
          </div>
        </div>
      )}
      <header>
        <h2 className="flex justify-between mb-8 text-lg font-medium text-gray-900 dark:text-gray-100">
          Vendor Details
          {user.vendor.status ==='pending' && (
            <span className="badge badge-warning">{user.vendor.status_label}</span>
          )}
          {user.vendor.status ==='rejected' && (
            <span className="badge badge-error">{user.vendor.status_label}</span>
          )}
          {user.vendor.status ==='approved' && (
            <span className="badge badge-success">{user.vendor.status_label}</span>
          )}
        </h2>
      </header>

      <div>
        {!user.vendor && (
          <PrimaryButton
            onClick={()=>toggleModal(true)}
            disabled={processing}>
            Become a vendor
          </PrimaryButton>
        )}
        {user.vendor &&(
          <>
            <form onSubmit={updateVendor}>
              <div className="mb-4">
                <InputLabel htmlFor="name" value="Store name" />
                <TextInput
                  id="name"
                  className="mt-1 block w-full"
                  value={data.store_name}
                  onChange={onStoreNameChange}
                  required
                  isFocused
                  autoComplete="name"
                />
                <InputError className="mt-2" message={errors.store_name} />
              </div>
              <div className="mb-4">
                <InputLabel htmlFor="address" value="Store Address" />
                <textarea
                  className="textarea textarea-bordered w-full mt-1"
                  value={data.store_address}
                  onChange={onStoreAddressChange}
                  placeholder="Enter your Store Address"></textarea>
                <InputError className="mt-2" message={errors.store_address} />
              </div>
              <div className="flex items-center gap-4">
                <PrimaryButton disabled={processing}>Update</PrimaryButton>
              </div>
            </form>
          </>
        )}
      </div>
    </section>
  )
}
export default VendorDetails
