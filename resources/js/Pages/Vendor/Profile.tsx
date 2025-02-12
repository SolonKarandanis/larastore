import {PageProps, PaginationProps, Product, Vendor} from '@/types';
import { Head, } from '@inertiajs/react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import ProductItem from "@/Components/App/ProductItem";

const Profile = ({vendor,products}:PageProps<{vendor:Vendor, products:PaginationProps<Product>}>) => {
  return (
    <AuthenticatedLayout>
      <Head title="Home"/>
      <div className="hero bg-gray-200 h-[300px] dark:bg-gray-950">
        <div className="hero-content text-center">
          <div className="max-w-md">
            <h1 className="text-5xl font-bold">Hello there</h1>
            <p className="py-6">
              Provident cupiditate voluptatem et in. Quaerat fugiat ut assumenda excepturi exercitationem
              quasi. In deleniti eaque aut repudiandae et a id nisi.
            </p>
            <button className="btn btn-primary">Get Started</button>
          </div>
        </div>
      </div>
      <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3 p-8">
        {products?.data.map(product=>(
          <ProductItem key={product.id} product={product} />
        ))}
      </div>
    </AuthenticatedLayout>
  )
}
export default Profile
