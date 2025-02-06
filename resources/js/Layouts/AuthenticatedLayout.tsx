import {usePage} from '@inertiajs/react';
import {PropsWithChildren, ReactNode, useEffect, useRef, useState} from 'react';
import Navbar from "@/Components/App/Navbar";

export default function AuthenticatedLayout({
    header,
    children
}: PropsWithChildren<{ header?: ReactNode }>) {
  const props = usePage().props
  const [successMessages, setSuccessMessages]= useState<any[]>([]);
  const timeoutRefs = useRef<{[key:number]:ReturnType<typeof setTimeout>}>({});

  useEffect(()=>{
    if(props.success.message){
      const newMessage = {
        ...props.success,
        id:props.success.time,
      };
      setSuccessMessages((previousMessages)=>[newMessage, ...previousMessages]);
      const timeoutId = setTimeout(()=>{
        setSuccessMessages((previousMessages)=>previousMessages.filter((msg)=>msg.id == newMessage.id));
        delete timeoutRefs.current[newMessage.id];
      },5000);

      timeoutRefs.current[newMessage.id] = timeoutId;
    }
  },[props.success]);
  return (
      <div className="min-h-screen bg-gray-100 dark:bg-gray-900">
        <Navbar />
        {props.error && <div className="container mx-auto">
          <div className="alert alert-error">
            {props.error}
          </div>
        </div>}
        {header && (
            <header className="bg-white shadow dark:bg-gray-800">
                <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    {header}
                </div>
            </header>
        )}
        {successMessages.length > 0 && (
          <div className="toast toast-top toast-end z-[1000] mt-16">
            {successMessages.map(msg=>(
              <div className="alert alert-success" key={msg.id}>
                <span>{msg.message}</span>
              </div>
            ))}
          </div>
        )}
        <main>{children}</main>
      </div>
  );
}
