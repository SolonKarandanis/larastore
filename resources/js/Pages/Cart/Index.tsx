import {CartItem, GroupedCartItem, PageProps} from "@/types";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {Head} from "@inertiajs/react";

const Index = ({
  totalPrice,
  csrf_token,
  items,
  totalQuantity}:PageProps<{items:Record<number,GroupedCartItem>}>) => {
  return (
    <AuthenticatedLayout>
      <Head title="Your Cart" />

    </AuthenticatedLayout>
  )
}
export default Index
