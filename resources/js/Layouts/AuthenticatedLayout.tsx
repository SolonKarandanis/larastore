import {usePage} from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useState } from 'react';
import Navbar from "@/Components/App/Navbar";

export default function AuthenticatedLayout({
    header,
    children
}: PropsWithChildren<{ header?: ReactNode }>) {
  const props = usePage().props
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
          <main>{children}</main>
        </div>
    );
}
