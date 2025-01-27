import {CartItem, GroupedCartItem, PageProps} from "@/types";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {Head, Link} from "@inertiajs/react";

const Index = ({
  totalPrice,
  csrf_token,
  cartItems,
  totalQuantity}:PageProps<{cartItems:Record<number,GroupedCartItem>}>) => {

  return (
    <AuthenticatedLayout>
      <Head title="Your Cart" />
      <div className="container mx-auto p-8 flex flex-col lg:flex-row gap-4">
        <div className="card flex-1 bg-white dark:bg-gray-800 order-2 lg:order-1">
          <div className="card-body">

          </div>
        </div>
        <div className="card bg-white dark:bg-gray-800 lg:min-w-[260px] order-1 lg:order-2">
          <div className="card-body">
            <h2 className="text-lg font-bold">
              Shopping Cart
            </h2>
            <div className="my-4">
              {cartItems && Object.keys(cartItems).length === 0 && (
                <div className="py-2 text-gray-500 text-center">
                  You don't have any items yet
                </div>
              )}
              {cartItems && Object.values(cartItems).length > 0 &&
                Object.values(cartItems).map(cartItem=>(
                  <div key={cartItem.user.id}>
                    <div className="flex items-center justify-between pb-4 border-b border-gray-300 mb-4">
                      <Link href="/" className="underline">
                        {cartItem.user.name}
                      </Link>
                      <div>
                        <form action={route('cart.checkout')} method="POST">
                          <input type="hidden" name="_token" value={csrf_token}/>
                          <input type="hidden" name="vendor_id" value={cartItem.user.id} />
                          <button className="btn btn-sm btn-ghost">
                            {/*<CreditCardIcon className ="size-6" />*/}
                            Pay Only for this Seller
                          </button>
                        </form>
                      </div>
                    </div>
                  </div>
                ))
              }
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  )
}
export default Index
