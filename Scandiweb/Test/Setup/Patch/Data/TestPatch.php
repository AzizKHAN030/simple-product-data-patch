<?php

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\App\State;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;


class TestPatch implements DataPatchInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var CategoryFactory
     */
    protected CategoryFactory $categoryFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    protected CategoryRepositoryInterface $categoryRepository;

    /**
     * @var StoreManagerInterface
     */

    protected StoreManagerInterface $storeManager;

    /**
     * @var CategoryLinkManagementInterface
     */

    protected CategoryLinkManagementInterface $categoryLink;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryFactory $categoryFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     * @param CategoryLinkManagementInterface $categoryLink
     * @param SourceItemInterfaceFactory $sourceItemFactory
     * @param State $state
     * @throws LocalizedException
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        CategoryFactory $categoryFactory,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        CategoryLinkManagementInterface $categoryLink,
        SourceItemInterfaceFactory $sourceItemFactory,
        State $state
    ){
        $this->moduleDataSetup = $moduleDataSetup;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->categoryLink = $categoryLink;
        $this->sourceItemFactory = $sourceItemFactory;
        $state->setAreaCode('adminhtml');
    }

    /**
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */

    public function apply(): void
    {
        $categoryId = array('20');

        $sku = 'TestSimpleWomen';

        $simpleProductArray = [
            [
                'sku' => $sku,
                'name' => 'Test Simple Women',
                'attribute_id' => '4',
                'status' => 1,
                'weight' => 2,
                'price' => 200,
                'visibility' => 4,
                'type_id' => 'simple',
            ]
        ];

        foreach ($simpleProductArray as $data) {
            // Create Product
            $product = $this->productFactory->create();
            $product->setSku($data['sku'])
                ->setName($data['name'])
                ->setAttributeSetId($data['attribute_id'])
                ->setStatus($data['status'])
                ->setWeight($data['weight'])
                ->setPrice($data['price'])
                ->setVisibility($data['visibility'])
                ->setTypeId($data['type_id'])
                ->setStockData(
                    array(
                        'manage_stock' => 1,
                        'is_in_stock' => 1
                    )
                );

            $sourceItem = $this->sourceItemFactory->create();
            $sourceItem->setSourceCode('default')
                ->setQuantity(199)
                ->setSku($product->getSku())
                ->setStatus(SourceItemInterface::STATUS_IN_STOCK);

            $this->sourceItems[] = $sourceItem;
            $this->sourceItemsSaveInterface->execute($this->sourceItems);

            $product = $this->productRepository->save($product);
            $product->save();
        }

        $this->categoryLink->assignProductToCategories($sku, $categoryId);
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
